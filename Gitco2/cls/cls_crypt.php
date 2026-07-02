<?php

class cls_crypt
{
  private $cryptKey;
  public function __construct()
  {
    $this->cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
  }
//  function encryptIt( $psw ) {
//  	$qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $this->cryptKey ), $psw, MCRYPT_MODE_CBC, md5( md5( $this->cryptKey ) ) ) );
//    //$qEncoded      = base64_encode( openssl_encrypt(  $psw,'aes-256-ctr', base64_decode($this->cryptKey),OPENSSL_ZERO_PADDING ) );
//  	return( $qEncoded );
//  }
//
//  function decryptIt( $psw ) {
//  	$qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $this->cryptKey ), base64_decode( $psw ), MCRYPT_MODE_CBC, md5( md5( $this->cryptKey ) ) ), "\0");
//    //$qDecoded      = rtrim( openssl_decrypt(  base64_decode( $psw ),'aes-256-ctr',base64_decode($this->cryptKey), OPENSSL_ZERO_PADDING), "\0");
//  	return( $qDecoded );
//  }

    //Return encrypted string
    public function encryptIt ($plainText) {

        $cipher   = 'aes-256-cbc';

        if (in_array($cipher, openssl_get_cipher_methods()))
        {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt(
                $plainText, $cipher, $this->cryptKey, $options=OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $this->cryptKey, $as_binary=true);
            $encodedText = base64_encode( $iv.$hmac.$ciphertext_raw );
        }

        return $encodedText;
    }


//Return decrypted string
    public function decryptIt ($encodedText) {

        $c = base64_decode($encodedText);
        $cipher   = 'aes-256-cbc';

        if (in_array($cipher, openssl_get_cipher_methods()))
        {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len=32);
            $ivlenSha2len = $ivlen+$sha2len;
            $ciphertext_raw = substr($c, $ivlen+$sha2len);
            $plainText = openssl_decrypt(
                $ciphertext_raw, $cipher, $this->cryptKey, $options=OPENSSL_RAW_DATA, $iv);
        }

        return $plainText;
    }


}


?>
