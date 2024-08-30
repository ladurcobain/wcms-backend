<?php
    namespace App\Helpers;

    class Status {

        public static function statusRating($value) {
            switch($value) {
                case 5 :
                    $string = "Sangat Puas";
                break;
                case 4 :
                    $string = "Puas";
                break;    
                case 3 :
                    $string = "Cukup Puas";
                break;
                case 2 :
                    $string = "Kurang Puas";
                break;
                case 1 :
                    $string = "Tidak Puas";
                break;
                default :
                    $string = "Puas";
                break;
            }
            
            return $string;
        }

        public static function medsosName($value) {
            switch($value) {
                case 1 :
                    $string = 'Facebook';
                break;
                case 2 :
                    $string = 'Twitter';
                break;
                case 3 :
                    $string = 'Instagram';
                break;
                case 4 :
                    $string = 'Youtube';
                break;
                case 5 :
                    $string = 'Linkedin';
                break;
                
                default :
                    $string = 'Facebook';
                break;
            }

            return $string;
        }

        public static function slugCharacters($str) {
            $str = strtolower($str);
            $str = str_replace(" ", "-", strip_tags($str));
            $str = str_replace("_", "-", $str);
            $str = str_replace("@", "", $str);
            $str = str_replace("#", "", $str);
            $str = str_replace("$", "", $str);
            $str = str_replace("%", "", $str);
            $str = str_replace("^", "", $str);
            $str = str_replace("&", "", $str);
            $str = str_replace("*", "", $str);
            $str = str_replace("'", "", $str);
            $str = str_replace('"', '', $str);
            $str = str_replace('!', '', $str);
            $str = str_replace('?', '', $str);
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '', $str);
            $str = str_replace('/', '', $str);
            $str = str_replace('(', '', $str);
            $str = str_replace('(', '', $str);
            $str = str_replace('[', '', $str);
            $str = str_replace(']', '', $str);
            $str = str_replace('{', '', $str);
            $str = str_replace('}', '', $str);
            $str = str_replace('<', '', $str);
            $str = str_replace('>', '', $str);
            
            return $str;
        }

        public static function htmlCharacters($str) {
            $str = strtolower($str);
            $str = str_replace("@", "", $str);
            $str = str_replace("#", "", $str);
            $str = str_replace("$", "", $str);
            $str = str_replace("%", "", $str);
            $str = str_replace("^", "", $str);
            $str = str_replace("&", "", $str);
            $str = str_replace("*", "", $str);
            $str = str_replace("'", "", $str);
            $str = str_replace('"', '', $str);
            $str = str_replace('!', '', $str);
            $str = str_replace('?', '', $str);
            $str = str_replace('/', '', $str);
            $str = str_replace('(', '', $str);
            $str = str_replace('(', '', $str);
            $str = str_replace('[', '', $str);
            $str = str_replace(']', '', $str);
            $str = str_replace('{', '', $str);
            $str = str_replace('}', '', $str);
            $str = str_replace('<', '', $str);
            $str = str_replace('>', '', $str);
            
            return $str;
        }

        public static function convertHtmlToText($str) {
            $str = strip_tags($str);
            $str = utf8_decode($str);
            $str = str_replace("&nbsp;", " ", $str);
            $str = preg_replace('/\s+/', ' ',$str);
            $str = trim($str);
    
            return $str;
        }
    
        public static function str_ellipsis($text, $length) {
            $text = strtolower($text);
            $text = Status::convertHtmlToText($text);
            if(strlen($text) > $length) {
                $str = substr($text, 0, $length) ." ...";
            }
            else {
                $str = $text;
            }
    
            return $str;
        }

        public static function str_url($text) {
            $text = strtolower($text);
            $text = Status::slugCharacters($text);
            
            $length = 50;
            if(strlen($text) > $length) {
                $str = substr($text, 0, $length);
            }
            else {
                $str = $text;
            }
    
            return $str;
        }

        public static function get_positition_of_char_in_string($char, $string) {
            $pos = -1;
            $positions = 0;
            
            while (($pos = strpos($string, $char, $pos+1)) !== false) {
                $positions = $pos;
            }
            
            return $positions;
        }
    
        public static function get_id_from_url($string) {
            $pos = Status::get_positition_of_char_in_string("@", $string);
            $stp = Status::get_positition_of_char_in_string("&", $string);

            $len = strlen($string);
            $str = substr($string, ($pos+1), ($stp-1));

            return $str;
        }

        public static function get_array_from_url($string) {
            $pos = Status::get_positition_of_char_in_string("@", $string);
            $stp = Status::get_positition_of_char_in_string("&", $string);

            $len = strlen($string);
            
            $d = substr($string, ($pos+1), ($stp-1));
            $e = substr($string, ($stp+1), ($len - $stp));
            
            $result = array(
                'code' => $d,
                'name' => $e
            );
            
            return $result;
        }

        public static function find_link_map($str) {
            $str = str_replace('<iframe src="', '', $str);
            $str = str_replace('</iframe>', '', $str);
            $str = str_replace('style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">', '', $str);

            $pure = strip_tags($str);
            
            $posHeight = Status::get_positition_of_char_in_string('=', $pure);
            $removeHeight = substr($pure, 0, $posHeight);
            $pure = str_replace('height', '', $removeHeight);
            
            $posWidth = Status::get_positition_of_char_in_string('=', $pure);
            $removeWidth = substr($pure, 0, $posWidth);
            $pure = str_replace('width', '', $removeWidth);

            $string = substr($pure, 0, (strlen($pure) - 2));
            return $string;
        }

        public static function youtube_embded($str) {
            $string = str_replace('watch', 'embed', $str);
            return $string;
        }

        public static function youtube_watch($str) {
            $string = str_replace('embed', 'watch', $str);
            return $string;
        }

        public static function monthName($value) {
            switch($value) {
                case "01" :
                    $string = 'Januari';
                break;
                case "02" :
                    $string = 'Februari';
                break;
                case "03" :
                    $string = 'Maret';
                break;
                case "04" :
                    $string = 'April';
                break;
                case "05" :
                    $string = 'Mei';
                break;
                case "06" :
                    $string = 'Juni';
                break;
                case "07" :
                    $string = 'Juli';
                break;
                case "08" :
                    $string = 'Agustus';
                break;
                case "09" :
                    $string = 'September';
                break;
                case "10" :
                    $string = 'Oktober';
                break;
                case "11" :
                    $string = 'November';
                break;
                case "12" :
                    $string = 'Desember';
                break;
                
                default :
                    $string = '-';
                break;
            }
    
            return $string;
        }
    }
?>