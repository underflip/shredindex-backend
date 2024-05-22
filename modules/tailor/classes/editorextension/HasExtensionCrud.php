<?php namespace Tailor\Classes\EditorExtension;

use Lang;
use Config;
use Request;
use BackendAuth;
use SystemException;
use ApplicationException;
use Tailor\Classes\EditorExtension;
use Tailor\Classes\Blueprint;
use Editor\Classes\ApiHelpers;
use Tailor\Classes\BlueprintIndexer;
use Tailor\Classes\BlueprintException;
use Tailor\Classes\BlueprintErrorData;

/**
 * HasExtensionCrud implements CRUD operations for the Tailor Editor Extension
 */
trait HasExtensionCrud
{
    /**
     * command_onOpenDocument
     */
    protected function command_onOpenDocument()
    {
        $documentData = post('documentData');
        if (!is_array($documentData)) {
            throw new SystemException('Document data is not provided');
        }

        $key = ApiHelpers::assertGetKey($documentData, 'key');
        $documentType = ApiHelpers::assertGetKey($documentData, 'type');
        $this->assertDocumentTypePermissions($documentType);

        $extraData = $this->getRequestExtraData();

        $isResetFromTemplateFileRequest = isset($extraData['resetFromTemplateFile']);
        if ($isResetFromTemplateFileRequest) {
            $this->resetFromTemplateFile($documentType, $key);
        }

        $template = $this->loadTemplate($documentType, $key);
        $templateData = [
            'content' => $template->content,
            'fileName' => ltrim($template->fileName, '/')
        ];

        $result = [
            'document' => $templateData,
            'metadata' => $this->loadTemplateMetadata($template, $documentData)
        ];

        return $result;
    }

    /**
     * command_onSaveDocument
     */
    protected function command_onSaveDocument()
    {
        $documentData = $this->getRequestDocumentData();
        $metadata = $this->getRequestMetadata();
        $documentType = ApiHelpers::assertGetKey($metadata, 'type');
        $this->assertDocumentTypePermissions($documentType);

        $templatePath = trim(ApiHelpers::assertGetKey($metadata, 'path'));
        $template = $this->loadOrCreateTemplate($documentType, $templatePath);
        $templateData = [];

        $fields = ['fileName', 'content'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $documentData)) {
                $templateData[$field] = $documentData[$field];
            }
        }

        $templateData = $this->handleLineEndings($templateData);
        if ($response = $this->handleMtimeMismatch($template, $metadata)) {
            return $response;
        }

        try {
            $template->content = $templateData['content'];
            $template->fileName = $templateData['fileName'];
            $originalContent = $template->content;
            $template->save();
        }
        catch (BlueprintException $ex) {
            return BlueprintErrorData::fromException($ex)->toResponse();
        }

        return $this->getUpdateResponse($template, $originalContent);
    }

    /**
     * command_onDeleteDocument
     */
    protected function command_onDeleteDocument()
    {
        $metadata = $this->getRequestMetadata();

        [$template, $documentType] = $this->loadRequestedTemplate($metadata);
        $this->assertDocumentTypePermissions($documentType);

        $template->delete();
    }

    /**
     * command_onMigrateBlueprint
     */
    protected function command_onMigrateBlueprint($controller)
    {
        $template = $this->loadBlueprintForUpdate();

        BlueprintIndexer::instance()->migrateBlueprint($template);

        return [
            'mainMenu' => $controller->makeLayoutPartial('mainmenu'),
            'mainMenuLeft' => $controller->makeLayoutPartial('mainmenu', ['isVerticalMenu'=>true]),
            'sidenavResponsive' => $controller->makeLayoutPartial('sidenav-responsive')
        ];
    }

    /**
     * command_onBlueprintCreateDirectory
     */
    protected function command_onBlueprintCreateDirectory()
    {
        $documentData = $this->getRequestDocumentData();
        // $metadata = $this->getRequestMetadata();

        $newName = trim(ApiHelpers::assertGetKey($documentData, 'name'));
        $parent = ApiHelpers::assertGetKey($documentData, 'parent');

        $this->editorCreateDirectory($this->getBlueprintsPath(), $newName, $parent);
    }

    /**
     * command_onBlueprintRename
     */
    protected function command_onBlueprintRename()
    {
        $documentData = $this->getRequestDocumentData();

        $newName = trim(ApiHelpers::assertGetKey($documentData, 'name'));
        $originalPath = trim(ApiHelpers::assertGetKey($documentData, 'originalPath'));
        $blueprintExtensions = ['yaml'];

        $this->editorRenameFileOrDirectory($this->getBlueprintsPath(), $newName, $originalPath, $blueprintExtensions);
    }

    /**
     * command_onBlueprintDelete
     */
    protected function command_onBlueprintDelete()
    {
        $documentData = $this->getRequestDocumentData();
        $fileList = ApiHelpers::assertGetKey($documentData, 'files');
        ApiHelpers::assertIsArray($fileList);

        $this->editorDeleteFileOrDirectory($this->getBlueprintsPath(), $fileList);
    }

    /**
     * command_onBlueprintDelete
     */
    protected function command_onBlueprintMove()
    {
        $documentData = $this->getRequestDocumentData();

        $selectedList = ApiHelpers::assertGetKey($documentData, 'source');
        $destinationDir = ApiHelpers::assertGetKey($documentData, 'destination');
        $this->editorMoveFilesOrDirectories($this->getBlueprintsPath(), $selectedList, $destinationDir);
    }

    protected function command_onBlueprintUpload()
    {
        $this->editorUploadFiles($this->getBlueprintsPath(), ['yaml']);
    }

    /**
     * Returns an existing template of a given type
     * @param string $documentType
     * @param string $path
     * @return mixed
     */
    private function loadTemplate($documentType, $path)
    {
        $class = $this->resolveTypeClassName($documentType);

        if (!($template = call_user_func([$class, 'load'], $path))) {
            throw new ApplicationException(trans('tailor::lang.blueprint.not_found'));
        }

        return $template;
    }

    /**
     * Resolves a template type to its class name
     * @param string $documentType
     * @return string
     */
    private function resolveTypeClassName($documentType)
    {
        $types = [
            EditorExtension::DOCUMENT_TYPE_BLUEPRINT     => Blueprint::class
        ];

        if (!array_key_exists($documentType, $types)) {
            throw new SystemException(trans('tailor::lang.editor.invalid_type'));
        }

        return $types[$documentType];
    }

    /**
     * makeMetadataForNewTemplate builds meta data for new templates
     */
    protected function makeMetadataForNewTemplate(string $documentType): array
    {
        return [
            'mtime' => null,
            'path' => null,
            'type' => $documentType,
            'isNewDocument' => true
        ];
    }

    private function loadTemplateMetadata($template, $documentData)
    {
        $typeNames = [
            EditorExtension::DOCUMENT_TYPE_BLUEPRINT => Lang::get('tailor::lang.editor.blueprint')
        ];

        $documentType = $documentData['type'];
        if (!array_key_exists($documentType, $typeNames)) {
            throw new SystemException(sprintf('Document type name is not defined: %s', $documentData['type']));
        }

        $fileName = ltrim($template->fileName, '/');

        $result = [
            'mtime' => $template->mtime,
            'path' => $fileName,
            'type' => $documentType,
            'typeName' => $typeNames[$documentType]
        ];

        return $result;
    }

    private function getRequestMetadata()
    {
        $metadata = Request::input('documentMetadata');
        if (!is_array($metadata)) {
            throw new SystemException('Invalid documentMetadata');
        }

        return $metadata;
    }

    private function getRequestExtraData()
    {
        $extraData = Request::input('extraData');
        if (!is_array($extraData)) {
            return [];
        }

        return $extraData;
    }

    private function getRequestDocumentData()
    {
        $documentData = Request::input('documentData');
        if (!is_array($documentData)) {
            throw new SystemException('Invalid documentData');
        }

        return $documentData;
    }

    private function createTemplate($documentType)
    {
        $class = $this->resolveTypeClassName($documentType);

        $template = new $class();

        return $template;
    }

    private function loadOrCreateTemplate($documentType, $templatePath)
    {
        if ($templatePath) {
            return $this->loadTemplate($documentType, $templatePath);
        }

        return $this->createTemplate($documentType);
    }

    private function handleLineEndings($templateData)
    {
        $convertLineEndings = Config::get('system.convert_line_endings', false) === true;
        if (!$convertLineEndings) {
            return $templateData;
        }

        if (!empty($templateData['content'])) {
            $templateData['content'] = $this->convertLineEndings($templateData['content']);
        }

        return $templateData;
    }

    /**
     * Replaces Windows style (/r/n) line endings with unix style (/n)
     * line endings.
     * @param string $markup The markup to convert to unix style endings
     * @return string
     */
    private function convertLineEndings($markup)
    {
        $markup = str_replace(["\r\n", "\r"], "\n", $markup);

        return $markup;
    }

    private function handleMtimeMismatch($template, $metadata)
    {
        $requestMtime = ApiHelpers::assertGetKey($metadata, 'mtime');

        if (!$template->mtime) {
            return;
        }

        if (post('documentForceSave')) {
            return;
        }

        if ($requestMtime != $template->mtime) {
            return ['mtimeMismatch' => true];
        }
    }

    private function getUpdateResponse($template, $originalContent)
    {
        $navigatorPath = dirname($template->fileName);
        if ($navigatorPath == '.') {
            $navigatorPath = "";
        }

        $result = [
            'metadata' => [
                'mtime' => $template->mtime,
                'path' => $template->fileName,
                'navigatorPath' => $navigatorPath,
                'uniqueKey' => $template->fileName,
                'fileName' => basename($template->fileName)
            ],
            'contentChanged' => $originalContent != $template->content
        ];

        $result['fileName'] = $template->fileName;

        return $result;
    }

    private function loadRequestedTemplate($metadata)
    {
        $metadata = $metadata ? $metadata : $this->getRequestMetadata();

        $documentType = ApiHelpers::assertGetKey($metadata, 'type');
        $templatePath = trim(ApiHelpers::assertGetKey($metadata, 'path'));

        return [
            $this->loadTemplate($documentType, $templatePath),
            $documentType
        ];
    }

    private function assertDocumentTypePermissions($documentType)
    {
        $user = BackendAuth::getUser();

        if (!EditorExtension::hasAccessToDocType($user, $documentType)) {
            throw new ApplicationException(Lang::get(
                'editor::lang.editor.error_no_doctype_permissions',
                ['doctype' => $documentType]
            ));
        }
    }

    private function getBlueprintsPath()
    {
        return (new Blueprint)->getBasePath();
    }

    private function loadBlueprintForUpdate()
    {
        $metadata = $this->getRequestMetadata();
        $documentType = ApiHelpers::assertGetKey($metadata, 'type');
        $this->assertDocumentTypePermissions($documentType);

        $templatePath = trim(ApiHelpers::assertGetKey($metadata, 'path'));
        return $this->loadTemplate($documentType, $templatePath);
    }
}
