<?php

$url = 'https://gateway.octobercms.com/composer/dist/october/media/october-media-v3.6.26-82aef0.zip';
$username = 'thomas.andrew.hansen@gmail.com';
$password = '0ZwN4AmtgZwHkZwH3YGSxZmt5L2LmLGuxATRlMTD1BGpjZwIxLmSzMGqzBJV0';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);  // We only want headers, not the body

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "HTTP Status Code: $http_code\n\n";
echo "Response Headers:\n";
echo $response;

if ($http_code == 200) {
    echo "\nSuccess! The URL is accessible.\n";
} else {
    echo "\nFailed to access the URL. Please check the response headers for more information.\n";
}
