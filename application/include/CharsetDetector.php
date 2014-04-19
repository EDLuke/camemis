<?

class CharsetDetector {

    private static $CHARSETS = array(
        "ISO_8859-1",
        "ISO_8859-15",
        "CP850"
    );
    private static $TESTCHARS = array(
        "€",
        "ä",
        "Ä",
        "ö",
        "Ö",
        "ü",
        "Ü",
        "ß"
    );

    public static function convert($string) {
        return self::__iconv($string, self::getCharset($string));
    }

    public static function getCharset($string) {
        $normalized = self::__normalize($string);
        if (!strlen($normalized))
            return "UTF-8";
        $best = "UTF-8";
        $charcountbest = 0;
        foreach (self::$CHARSETS as $charset) {
            $str = self::__iconv($normalized, $charset);
            $charcount = 0;
            $stop = mb_strlen($str, "UTF-8");

            for ($idx = 0; $idx < $stop; $idx++) {
                $char = mb_substr($str, $idx, 1, "UTF-8");
                foreach (self::$TESTCHARS as $testchar) {

                    if ($char == $testchar) {

                        $charcount++;
                        break;
                    }
                }
            }
            if ($charcount > $charcountbest) {
                $charcountbest = $charcount;
                $best = $charset;
            }
            //echo $text."<br />";
        }
        return $best;
    }

    private static function __normalize($str) {

        $len = strlen($str);
        $ret = "";
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247))
                    $ret .=$str[$i];
                elseif ($c > 239)
                    $bytes = 4;
                elseif ($c > 223)
                    $bytes = 3;
                elseif ($c > 191)
                    $bytes = 2;
                else
                    $ret .=$str[$i];
                if (($i + $bytes) > $len)
                    $ret .=$str[$i];
                $ret2 = $str[$i];
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        $ret .=$ret2;
                        $ret2 = "";
                        $i+=$bytes - 1;
                        $bytes = 1;
                        break;
                    } else
                        $ret2.=$str[$i];
                    $bytes--;
                }
            }
        }
        return $ret;
    }

    private static function __iconv($string, $charset) {
        return iconv($charset, "UTF-8", $string);
    }

}
?>