/*
 * This file has been compiled from: /modules/system/lang/id/client.php
 */
if (!window.oc) {
    window.oc = {};
}

if (!window.oc.langMessages) {
    window.oc.langMessages = {};
}

window.oc.langMessages['id'] = $.extend(
    window.oc.langMessages['id'] || {},
    {"markdowneditor":{"formatting":"Formatting","quote":"Quote","code":"Code","header1":"Header 1","header2":"Header 2","header3":"Header 3","header4":"Header 4","header5":"Header 5","header6":"Header 6","bold":"Bold","italic":"Italic","unorderedlist":"Unordered List","orderedlist":"Ordered List","snippet":"Snippet","video":"Video","image":"Image","link":"Link","horizontalrule":"Insert Horizontal Rule","fullscreen":"Full Screen","preview":"Preview","strikethrough":"Strikethrough","cleanblock":"Clean Block","table":"Table","sidebyside":"Side by Side"},"mediamanager":{"insert_link":"Insert Media Link","insert_image":"Insert Media Image","insert_video":"Insert Media Video","insert_audio":"Insert Media Audio","invalid_file_empty_insert":"Please select file to insert a links to.","invalid_file_single_insert":"Please select a single file.","invalid_image_empty_insert":"Please select image(s) to insert.","invalid_video_empty_insert":"Please select a video file to insert.","invalid_audio_empty_insert":"Please select an audio file to insert."},"alert":{"error":"Error","confirm":"Confirm","dismiss":"Dismiss","confirm_button_text":"OK","cancel_button_text":"Cancel","widget_remove_confirm":"Remove this widget?"},"datepicker":{"previousMonth":"Previous Month","nextMonth":"Next Month","months":["January","February","March","April","May","June","July","August","September","October","November","December"],"weekdays":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"weekdaysShort":["Sun","Mon","Tue","Wed","Thu","Fri","Sat"]},"colorpicker":{"choose":"OK"},"filter":{"group":{"all":"all"},"scopes":{"apply_button_text":"Apply","clear_button_text":"Clear"},"dates":{"all":"all","filter_button_text":"Filter","reset_button_text":"Reset","date_placeholder":"Date","after_placeholder":"After","before_placeholder":"Before"},"numbers":{"all":"all","filter_button_text":"Filter","reset_button_text":"Reset","min_placeholder":"Min","max_placeholder":"Max"}},"eventlog":{"show_stacktrace":"Show the stacktrace","hide_stacktrace":"Hide the stacktrace","tabs":{"formatted":"Formatted","raw":"Raw"},"editor":{"title":"Source Code Editor","description":"Your operating system should be configured to listen to one of these URL schemes.","openWith":"Open with","remember_choice":"Remember selected option for this session","open":"Open","cancel":"Cancel"}},"upload":{"max_files":"You can not upload any more files.","invalid_file_type":"You can't upload files of this type.","file_too_big":"File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.","response_error":"Server responded with {{statusCode}} code.","remove_file":"Remove file"},"inspector":{"add":"Add","remove":"Remove","key":"Key","value":"Value","ok":"OK","cancel":"Cancel","items":"Items"}}
);


//! moment.js locale configuration v2.22.2

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';


    var id = moment.defineLocale('id', {
        months : 'Januari_Februari_Maret_April_Mei_Juni_Juli_Agustus_September_Oktober_November_Desember'.split('_'),
        monthsShort : 'Jan_Feb_Mar_Apr_Mei_Jun_Jul_Agt_Sep_Okt_Nov_Des'.split('_'),
        weekdays : 'Minggu_Senin_Selasa_Rabu_Kamis_Jumat_Sabtu'.split('_'),
        weekdaysShort : 'Min_Sen_Sel_Rab_Kam_Jum_Sab'.split('_'),
        weekdaysMin : 'Mg_Sn_Sl_Rb_Km_Jm_Sb'.split('_'),
        longDateFormat : {
            LT : 'HH.mm',
            LTS : 'HH.mm.ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY [pukul] HH.mm',
            LLLL : 'dddd, D MMMM YYYY [pukul] HH.mm'
        },
        meridiemParse: /pagi|siang|sore|malam/,
        meridiemHour : function (hour, meridiem) {
            if (hour === 12) {
                hour = 0;
            }
            if (meridiem === 'pagi') {
                return hour;
            } else if (meridiem === 'siang') {
                return hour >= 11 ? hour : hour + 12;
            } else if (meridiem === 'sore' || meridiem === 'malam') {
                return hour + 12;
            }
        },
        meridiem : function (hours, minutes, isLower) {
            if (hours < 11) {
                return 'pagi';
            } else if (hours < 15) {
                return 'siang';
            } else if (hours < 19) {
                return 'sore';
            } else {
                return 'malam';
            }
        },
        calendar : {
            sameDay : '[Hari ini pukul] LT',
            nextDay : '[Besok pukul] LT',
            nextWeek : 'dddd [pukul] LT',
            lastDay : '[Kemarin pukul] LT',
            lastWeek : 'dddd [lalu pukul] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'dalam %s',
            past : '%s yang lalu',
            s : 'beberapa detik',
            ss : '%d detik',
            m : 'semenit',
            mm : '%d menit',
            h : 'sejam',
            hh : '%d jam',
            d : 'sehari',
            dd : '%d hari',
            M : 'sebulan',
            MM : '%d bulan',
            y : 'setahun',
            yy : '%d tahun'
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 1st is the first week of the year.
        }
    });

    return id;

})));


/*! Select2 4.1.0-rc.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/id",[],function(){return{errorLoading:function(){return"Data tidak boleh diambil."},inputTooLong:function(n){return"Hapuskan "+(n.input.length-n.maximum)+" huruf"},inputTooShort:function(n){return"Masukkan "+(n.minimum-n.input.length)+" huruf lagi"},loadingMore:function(){return"Mengambil data…"},maximumSelected:function(n){return"Anda hanya dapat memilih "+n.maximum+" pilihan"},noResults:function(){return"Tidak ada data yang sesuai"},searching:function(){return"Mencari…"},removeAllItems:function(){return"Hapus semua item"}}}),n.define,n.require}();

/*!
 * Froala Editor for October CMS
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = function( root, jQuery ) {
            if ( jQuery === undefined ) {
                // require('jQuery') returns a factory that requires window to
                // build a jQuery instance, we normalize how we use modules
                // that require this pattern but the window provided is a noop
                // if it's defined (how jquery works)
                if ( typeof window !== 'undefined' ) {
                    jQuery = require('jquery');
                }
                else {
                    jQuery = require('jquery')(root);
                }
            }
            return factory(jQuery);
        };
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
/**
 * Indonesian
 */

if (!$.FE_LANGUAGE) {
    $.FE_LANGUAGE = {};
}

$.FE_LANGUAGE['id'] = {
  translation: {
    // Place holder
    "Type something": "Ketik sesuatu",

    // Basic formatting
    "Bold": "Tebal",
    "Italic": "Miring",
    "Underline": "Garis bawah",
    "Strikethrough": "Coret",

    // Main buttons
    "Insert": "Memasukkan",
    "Delete": "Hapus",
    "Cancel": "Batal",
    "OK": "Ok",
    "Back": "Kembali",
    "Remove": "Hapus",
    "More": "Lebih",
    "Update": "Memperbarui",
    "Style": "Gaya",

    // Font
    "Font Family": "Jenis Huruf",
    "Font Size": "Ukuran leter",

    // Colors
    "Colors": "Warna",
    "Background": "Latar belakang",
    "Text": "Teks",
    "HEX Color": "Warna hex",

    // Paragraphs
    "Paragraph Format": "Format",
    "Normal": "Normal",
    "Code": "Kode",
    "Heading 1": "Header 1",
    "Heading 2": "Header 2",
    "Heading 3": "Header 3",
    "Heading 4": "Header 4",

    // Style
    "Paragraph Style": "Paragraf gaya",
    "Inline Style": "Di barisan gaya",

    // Alignment
    "Align": "Rate",
    "Align Left": "Rate kiri",
    "Align Center": "Rate tengah",
    "Align Right": "Rata kanan",
    "Align Justify": "Justifi",
    "None": "Tak satupun",

    // Lists
    "Ordered List": "List nomor",
    "Default": "Standar",
    "Lower Alpha": "Alpha lebih rendah",
    "Lower Greek": "Yunani lebih rendah",
    "Lower Roman": "Roman rendah",
    "Upper Alpha": "Alpha atas",
    "Upper Roman": "Roman atas",

    "Unordered List": "List simbol",
    "Circle": "Lingkaran",
    "Disc": "Cakram",
    "Square": "Kotak",

    // Line height
    "Line Height": "Tinggi garis",
    "Single": "Tunggal",
    "Double": "Dua kali lipat",

    // Indent
    "Decrease Indent": "Turunkan inden",
    "Increase Indent": "Tambah inden",

    // Links
    "Insert Link": "Memasukkan link",
    "Open in new tab": "Buka di tab baru",
    "Open Link": "Buka tautan",
    "Edit Link": "Mengedit link",
    "Unlink": "Menghapus link",
    "Choose Link": "Memilih link",

    // Images
    "Insert Image": "Memasukkan gambar",
    "Upload Image": "Meng-upload gambar",
    "By URL": "Oleh URL",
    "Browse": "Melihat-lihat",
    "Drop image": "Jatuhkan gambar",
    "or click": "atau klik",
    "Manage Images": "Mengelola gambar",
    "Loading": "Pemuatan",
    "Deleting": "Menghapus",
    "Tags": "Label",
    "Are you sure? Image will be deleted.": "Apakah Anda yakin? Gambar akan dihapus.",
    "Replace": "Mengganti",
    "Uploading": "Gambar upload",
    "Loading image": "Pemuatan gambar",
    "Display": "Pameran",
    "Inline": "Di barisan",
    "Break Text": "Memecah teks",
    "Alternative Text": "Teks alternatif",
    "Change Size": "Ukuran perubahan",
    "Width": "Lebar",
    "Height": "Tinggi",
    "Something went wrong. Please try again.": "Ada yang salah. Silakan coba lagi.",
    "Image Caption": "Keterangan gambar",
    "Advanced Edit": "Edit lanjutan",

    // Video
    "Insert Video": "Memasukkan video",
    "Embedded Code": "Kode tertanam",
    "Paste in a video URL": "Paste di url video",
    "Drop video": "Jatuhkan video",
    "Your browser does not support HTML5 video.": "Browser Anda tidak mendukung video html5.",
    "Upload Video": "Mengunggah video",

    // Tables
    "Insert Table": "Sisipkan tabel",
    "Table Header": "Header tabel",
    "Remove Table": "Hapus tabel",
    "Table Style": "Gaya tabel",
    "Horizontal Align": "Menyelaraskan horisontal",

    "Row": "Baris",
    "Insert row above": "Sisipkan baris di atas",
    "Insert row below": "Sisipkan baris di bawah",
    "Delete row": "Hapus baris",
    "Column": "Kolom",
    "Insert column before": "Sisipkan kolom sebelumSisipkan kolom sebelum",
    "Insert column after": "Sisipkan kolom setelah",
    "Delete column": "Hapus kolom",
    "Cell": "Sel",
    "Merge cells": "Menggabungkan sel",
    "Horizontal split": "Perpecahan horisontal",
    "Vertical split": "Perpecahan vertikal",
    "Cell Background": "Latar belakang sel",
    "Vertical Align": "Menyelaraskan vertikal",
    "Top": "Teratas",
    "Middle": "Tengah",
    "Bottom": "Bagian bawah",
    "Align Top": "Menyelaraskan atas",
    "Align Middle": "Menyelaraskan tengah",
    "Align Bottom": "Menyelaraskan bawah",
    "Cell Style": "Gaya sel",

    // Files
    "Upload File": "Meng-upload berkas",
    "Drop file": "Jatuhkan berkas",

    // Emoticons
    "Emoticons": "Emoticon",
    "Grinning face": "Sambil tersenyum wajah",
    "Grinning face with smiling eyes": "Sambil tersenyum wajah dengan mata tersenyum",
    "Face with tears of joy": "Hadapi dengan air mata sukacita",
    "Smiling face with open mouth": "Tersenyum wajah dengan mulut terbuka",
    "Smiling face with open mouth and smiling eyes": "Tersenyum wajah dengan mulut terbuka dan tersenyum mata",
    "Smiling face with open mouth and cold sweat": "Tersenyum wajah dengan mulut terbuka dan keringat dingin",
    "Smiling face with open mouth and tightly-closed eyes": "Tersenyum wajah dengan mulut terbuka dan mata tertutup rapat",
    "Smiling face with halo": "Tersenyum wajah dengan halo",
    "Smiling face with horns": "Tersenyum wajah dengan tanduk",
    "Winking face": "Mengedip wajah",
    "Smiling face with smiling eyes": "Tersenyum wajah dengan mata tersenyum",
    "Face savoring delicious food": "Wajah menikmati makanan lezat",
    "Relieved face": "Wajah Lega",
    "Smiling face with heart-shaped eyes": "Tersenyum wajah dengan mata berbentuk hati",
    "Smiling face with sunglasses": "Tersenyum wajah dengan kacamata hitam",
    "Smirking face": "Menyeringai wajah",
    "Neutral face": "Wajah Netral",
    "Expressionless face": "Wajah tanpa ekspresi",
    "Unamused face": "Wajah tidak senang",
    "Face with cold sweat": "Muka dengan keringat dingin",
    "Pensive face": "Wajah termenung",
    "Confused face": "Wajah Bingung",
    "Confounded face": "Wajah kesal",
    "Kissing face": "wajah mencium",
    "Face throwing a kiss": "Wajah melempar ciuman",
    "Kissing face with smiling eyes": "Berciuman wajah dengan mata tersenyum",
    "Kissing face with closed eyes": "Berciuman wajah dengan mata tertutup",
    "Face with stuck out tongue": "Muka dengan menjulurkan lidah",
    "Face with stuck out tongue and winking eye": "Muka dengan menjulurkan lidah dan mengedip mata",
    "Face with stuck out tongue and tightly-closed eyes": "Wajah dengan lidah terjebak dan mata erat-tertutup",
    "Disappointed face": "Wajah kecewa",
    "Worried face": "Wajah Khawatir",
    "Angry face": "Wajah Marah",
    "Pouting face": "Cemberut wajah",
    "Crying face": "Menangis wajah",
    "Persevering face": "Tekun wajah",
    "Face with look of triumph": "Hadapi dengan tampilan kemenangan",
    "Disappointed but relieved face": "Kecewa tapi lega wajah",
    "Frowning face with open mouth": "Sambil mengerutkan kening wajah dengan mulut terbuka",
    "Anguished face": "Wajah sedih",
    "Fearful face": "Wajah Takut",
    "Weary face": "Wajah lelah",
    "Sleepy face": "wajah mengantuk",
    "Tired face": "Wajah Lelah",
    "Grimacing face": "Sambil meringis wajah",
    "Loudly crying face": "Keras menangis wajah",
    "Face with open mouth": "Hadapi dengan mulut terbuka",
    "Hushed face": "Wajah dipetieskan",
    "Face with open mouth and cold sweat": "Hadapi dengan mulut terbuka dan keringat dingin",
    "Face screaming in fear": "Hadapi berteriak dalam ketakutan",
    "Astonished face": "Wajah Kaget",
    "Flushed face": "Wajah memerah",
    "Sleeping face": "Tidur face",
    "Dizzy face": "Wajah pusing",
    "Face without mouth": "Wajah tanpa mulut",
    "Face with medical mask": "Hadapi dengan masker medis",

    // Line breaker
    "Break": "Memecah",

    // Math
    "Subscript": "Subskrip",
    "Superscript": "Superskrip",

    // Full screen
    "Fullscreen": "Layar penuh",

    // Horizontal line
    "Insert Horizontal Line": "Sisipkan Garis Horizontal",

    // Clear formatting
    "Clear Formatting": "Menghapus format",

    // Save
    "Save": "Menyimpan",

    // Undo, redo
    "Undo": "Batal",
    "Redo": "Ulang",

    // Select all
    "Select All": "Pilih semua",

    // Code view
    "Code View": "Melihat kode",

    // Quote
    "Quote": "Kutipan",
    "Increase": "Meningkat",
    "Decrease": "Penurunan",

    // Quick Insert
    "Quick Insert": "Memasukkan cepat",

    // Spcial Characters
    "Special Characters": "Karakter spesial",
    "Latin": "Latin",
    "Greek": "Yunani",
    "Cyrillic": "Kyrillic",
    "Punctuation": "Tanda baca",
    "Currency": "Mata uang",
    "Arrows": "Panah",
    "Math": "Matematika",
    "Misc": "Misc",

    // Print.
    "Print": "Mencetak",

    // Spell Checker.
    "Spell Checker": "Pemeriksa ejaan",

    // Help
    "Help": "Membantu",
    "Shortcuts": "Jalan pintas",
    "Inline Editor": "Editor inline",
    "Show the editor": "Tunjukkan editornya",
    "Common actions": "Tindakan umum",
    "Copy": "Salinan",
    "Cut": "Memotong",
    "Paste": "Pasta",
    "Basic Formatting": "Format dasar",
    "Increase quote level": "Meningkatkan tingkat kutipan",
    "Decrease quote level": "Menurunkan tingkat kutipan",
    "Image / Video": "Gambar / video",
    "Resize larger": "Mengubah ukuran lebih besar",
    "Resize smaller": "Mengubah ukuran lebih kecil",
    "Table": "Meja",
    "Select table cell": "Pilih sel tabel",
    "Extend selection one cell": "Memperpanjang seleksi satu sel",
    "Extend selection one row": "Perpanjang pilihan satu baris",
    "Navigation": "Navigasi",
    "Focus popup / toolbar": "Fokus popup / toolbar",
    "Return focus to previous position": "Kembali fokus ke posisi sebelumnya",

    // Embed.ly
    "Embed URL": "Embed url",
    "Paste in a URL to embed": "Paste di url untuk menanamkan",

    // Word Paste.
    "The pasted content is coming from a Microsoft Word document. Do you want to keep the format or clean it up?": "Konten yang disisipkan berasal dari dokumen kata microsoft. apakah Anda ingin menyimpan format atau membersihkannya?",
    "Keep": "Menjaga",
    "Clean": "Bersih",
    "Word Paste Detected": "Kata paste terdeteksi"
  },
  direction: "ltr"
};

}));

