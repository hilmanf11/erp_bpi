<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Convertcurrency
{
    public function convertCurrencyToWords($amount, $currency) {
        $words = "";
    
        $currencyNames = array(
            "USD" => "Dollar",
            "EUR" => "Euro",
            "JPY" => "Yen",
            "IDR" => "Rupiah",
            // Add other currencies as needed
        );
    
        $words .= $this->numberToWords($amount) . " " . $currencyNames[$currency];
    
        return $words;
    }
    
    function numberToWords($number) {
        $words = "";
        $number = abs($number);
        $decimal = round($number - intval($number), 2) * 100;
    
        $numberInWords = array(
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety',
            100 => 'Hundred',
            1000 => 'Thousand',
            1000000 => 'Million',
            1000000000 => 'Billion'
        );
    
        if ($number < 20) {
            $words = $numberInWords[$number];
        } elseif ($number < 100) {
            $words = $numberInWords[10 * floor($number / 10)];
            $remainder = $number % 10;
            if ($remainder > 0) {
                $words .= " " . $numberInWords[$remainder];
            }
        } elseif ($number < 1000) {
            $words = $numberInWords[floor($number / 100)] . " " . $numberInWords[100];
            $remainder = $number % 100;
            if ($remainder > 0) {
                $words .= " and " . $this->numberToWords($remainder);
            }
        } elseif ($number < 1000000) {
            $words = $this->numberToWords(floor($number / 1000)) . " " . $numberInWords[1000];
            $remainder = $number % 1000;
            if ($remainder > 0) {
                $words .= ", " . $this->numberToWords($remainder);
            }
        } elseif ($number < 1000000000) {
            $words = $this->numberToWords(floor($number / 1000000)) . " " . $numberInWords[1000000];
            $remainder = $number % 1000000;
            if ($remainder > 0) {
                $words .= ", " . $this->numberToWords($remainder);
            }
        } else {
            $words = "Value is too large";
        }
    
        if ($decimal > 0) {
            $words .= " Point " . $this->numberToWords($decimal);
        }
    
        return $words;
    }
}
