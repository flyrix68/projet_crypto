<?php

class RSA {
    private $p;
    private $q;
    private $n;
    private $phi;
    private $e;
    private $d;

    public function __construct($p = null, $q = null) {
        if ($p && $q) {
            $this->generateKeys($p, $q);
        }
    }

    public function generateKeys($p, $q) {
        $this->p = $p;
        $this->q = $q;
        $this->n = $p * $q;
        $this->phi = ($p - 1) * ($q - 1);

        // Choose e (public exponent)
        $this->e = 65537; // Common choice

        // Calculate d (private exponent)
        $this->d = $this->modInverse($this->e, $this->phi);
    }

    public function encrypt($message, $e, $n) {
        $messageInt = $this->stringToInt($message);
        $encrypted = bcpowmod($messageInt, $e, $n);
        return $encrypted;
    }

    public function decrypt($encrypted, $d, $n) {
        $decryptedInt = bcpowmod($encrypted, $d, $n);
        return $this->intToString($decryptedInt);
    }

    public function getPublicKey() {
        return ['e' => $this->e, 'n' => $this->n];
    }

    public function getPrivateKey() {
        return ['d' => $this->d, 'n' => $this->n];
    }

    private function modInverse($a, $m) {
        $m0 = $m;
        $y = 0;
        $x = 1;

        if ($m == 1) {
            return 0;
        }

        while ($a > 1) {
            $q = bcdiv($a, $m);
            $t = $m;
            $m = bcmod($a, $m);
            $a = $t;
            $t = $y;
            $y = bcsub($x, bcmul($q, $y));
            $x = $t;
        }

        if ($x < 0) {
            $x = bcadd($x, $m0);
        }

        return $x;
    }

    private function stringToInt($string) {
        $result = '0';
        $bytes = unpack('C*', $string);
        foreach ($bytes as $byte) {
            $result = bcadd(bcmul($result, '256'), $byte);
        }
        return $result;
    }

    private function intToString($int) {
        $result = '';
        while (bccomp($int, '0') > 0) {
            $byte = bcmod($int, '256');
            $result = chr($byte) . $result;
            $int = bcdiv($int, '256');
        }
        return $result;
    }

    public static function isPrime($num) {
        if ($num < 2) return false;
        if ($num == 2) return true;
        if (bcmod($num, '2') == 0) return false;

        $sqrt = bcsqrt($num);
        for ($i = 3; bccomp($i, $sqrt) <= 0; $i = bcadd($i, '2')) {
            if (bcmod($num, $i) == 0) return false;
        }
        return true;
    }

    public static function generatePrime($bits = 512) {
        do {
            $num = self::generateRandomOdd($bits);
        } while (!self::isPrime($num));
        return $num;
    }

    private static function generateRandomOdd($bits) {
        $bytes = ceil($bits / 8);
        $random = openssl_random_pseudo_bytes($bytes);
        $hex = bin2hex($random);
        $num = gmp_init($hex, 16);
        if (gmp_cmp($num, gmp_pow(2, $bits - 1)) < 0) {
            $num = gmp_add($num, gmp_pow(2, $bits - 1));
        }
        $num = gmp_or($num, 1); // Make odd
        return gmp_strval($num);
    }
}
?>