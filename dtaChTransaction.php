<?php

/**
 * Klasse zum Erzeugen von DTA Transaktionen im schweizer Six Interbank Clearing
 * Format.
 *
 * @author Christoph Vieth <cvieth@coreweb.de>
 */
class dtaChTransaction {

    const TA827 = 827;
    const TA890 = 890;

    /**
     * Füllzeichen
     * @var char 
     */
    private $fillChar = ' ';

    /**
     * Typ des Records
     * @var string
     */
    private $type = NULL;

    /**
     * DTA-ID
     * @var string
     */
    private $dtaId = NULL;

    /**
     * Zu belastendes Konto
     * @var string
     */
    private $debitAccount = NULL;

    /**
     * Vergütungsbetrag
     * @var string
     */
    private $paymentAmount = NULL;

    /**
     * Vergütungsbetrag in Numerischer Darstellung
     * @var float
     */
    private $paymentAmountNumeric = NULL;

    /**
     * Eingabe-Sequenznummer
     * @var int
     */
    private $inputSequenceNr = NULL;

    /**
     * Bankenclearing-Nr. der bank des Auftraggebers
     * @var int
     */
    private $clientClearingNr = NULL;

    /**
     * Erstellungsdatum
     * @var string
     */
    private $creationDate = NULL;

    /**
     * Gewünschter Verarbeitungstag
     * @var string
     */
    private $processingDay = NULL;

    /**
     * Auftraggeber
     * @var array 
     */
    private $client = NULL;

    /**
     * Begünstigter
     * @var array
     */
    private $recipient = NULL;

    /**
     * Zahlungsgrund
     * @var array
     */
    private $paymentReason = NULL;

    /**
     * Totalbetrag
     * @var string
     */
    private $totalAmount;

    public function __construct($transactionType) {
        $avaliableTypes = array(self::TA827);
        if (!in_array($transactionType, $avaliableTypes))
            throw new Exception("Transaktionstyp nicht bekannt oder nicht implementiert!");
        else
            $this->type = $transactionType;
    }

    public function test() {
        var_dump($this->genTA827());
    }

    public function toString() {
        switch ($this->type) {
            case self::TA827:
                $record = $this->genTA827();
                break;
            case self::TA890:
                $record = $this->genTA890();
            default:
                throw new Exception("Transaktionstyp nicht nicht implementiert!");
                break;
        }
        $string = '';
        while ($segment = array_pop($record)) {
            $string = $segment . "\n" . $string;
        }
        return $string;
    }

    private function isIsoCurrencyCode($currencyCode) {
        /**
         * @todo Weitere ISO-Währungscodes einpflegen
         */
        $validCodes = array('CHF', 'EUR');
        if (in_array($currencyCode, $validCodes))
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Erzeugt eine TA827 Transaktion
     * 
     * @return array
     */
    private function genTA827() {
        $record = array();
        // Segment 01
        $segment01 = '01'
                . $this->getHeader()
                . $this->getReferenceNr()
                . $this->getDebitAccount()
                . $this->getPaymentAmount()
                . $this->getReserve(14);
        array_push($record, $segment01);

        // Segment 02
        $segment02 = '02'
                . $this->getClient()
                . $this->getReserve(30);
        array_push($record, $segment02);

        // segment 03
        $segment03 = '03'
                . $this->getRecipient();
        array_push($record, $segment03);

        // segment 04
        $segment04 = '04'
                . $this->getPaymentReason()
                . $this->getReserve(14);
        array_push($record, $segment04);

        // segment 05
        $segment05 = '05'
                . $this->getEndRecipient();
        array_push($record, $segment05);

        return $record;
    }

    /**
     * Erzeugt eine Transaktion vom Typ TA890 (Totalrecord)
     * 
     * @return array
     */
    private function genTA890() {
        $record = array();
        $segment01 = '01'
                . $this->getHeader()
                . $this->getTotalAmount()
                . $this->getReserve(59);
        array_push($record, $segment01);
        return $record;
    }
    
    /**
     * Setzt den Totalbetrag
     * 
     * @param float|int $amount
     * @throws Exception
     */
    public function setTotalAmount($amount) {
        // Überprüfen des Betrages
        if (!((is_float($amount)) || (is_integer($amount))))
            throw new Exception("Der übergebene Betrag muss Eine Zahl sein!");
        else
            $this->totalAmount = str_pad(number_format($amount, 3, ',', ''), 16, $this->fillChar);
    }

    /**
     * Fragt den Totalbetrag ab
     * 
     * @return string
     * @throws Exception
     */
    private function getTotalAmount() {
        if ($this->totalAmount == NULL)
            throw new Exception("Vergütungsbetrag nicht gesetzt!");
        elseif (strlen($this->totalAmount) != 16)
            throw new Exception("Gesetzter Vergütungsbetrag hat ungültige Länge!");
        else
            return $this->totalAmount;
    }

    private function getReserve($length) {
        $reserve = '';
        for ($i = 1; $i <= $length; $i++) {
            $reserve .= $this->fillChar;
        }
        return $reserve;
    }

    private function getHeader() {
        $header = $this->getProcessingDay()
                . $this->getRecipientClearingNr()
                . $this->getOutputSequenceNr()
                . $this->getCreationDate()
                . $this->getClientClearingNr()
                . $this->getDtaId()
                . $this->getInputSequenceNr()
                . $this->getTransactionType()
                . $this->getPaymentType()
                . $this->getProcessingFlag();
        return $header;
    }

    public function setProcessingDay($processingDay) {
        if (($this->type != self::TA827) || ($this->type != self::TA827))
            throw new Exception("Gewünschter Verarbeitungstag ist nur bei TA826 und TA827 zu setzen");
        elseif ((!is_numeric($processingDay)) && (!(strlen($processingDay) == 6)))
            throw new Exception("Gewünschter Verarbeitungstag muss ein Datum im Format JJMMTT sein!");
        else
            $this->processingDay = $processingDay;
    }

    private function getProcessingDay() {
        if (($this->type == self::TA827) || ($this->type == self::TA827)) {
            if ($this->processingDay == NULL)
                throw new Exception("Gewünschter Verarbeitungstag nicht gesetzt!");
            else
                return $this->processingDay;
        } else
            return '000000';
    }

    private function getRecipientClearingNr() {
        return $this->getReserve(12);
    }

    private function getOutputSequenceNr() {
        return '00000';
    }

    public function setCreationDate($creationDate) {
        if ((!is_numeric($creationDate)) && (!(strlen($creationDate) == 6)))
            throw new Exception("Valuta muss ein Datum im Format JJMMTT sein!");
        else
            $this->creationDate = $creationDate;
    }

    private function getCreationDate() {
        if ($this->creationDate == NULL)
            throw new Exception("Erstellungsdatum nicht gesetzt!");
        else
            return $this->creationDate;
    }

    public function setClientClearingNr($clearingNr) {
        if (!is_integer($clearingNr))
            throw new Exception("Übergebene Clearing-Nr der Bank des Auftraggebers ist ungültig!");
        else
            $this->clientClearingNr = $clearingNr;
    }

    private function getClientClearingNr() {
        if ($this->type == self::TA890)
            return $this->getReserve(7);
        elseif ($this->clientClearingNr == NULL)
            throw new Exception("Clearing-Nr der Bank des Auftraggebers nicht gesetzt!");
        else
            return str_pad($this->clientClearingNr, 7, $this->fillChar);
    }

    public function setInputSequenceNr($sequenceNr) {
        if (!is_integer($sequenceNr))
            throw new Exception("Übergebene Eingabe-Sequenznummer ist ungültig!");
        else
            $this->inputSequenceNr = $sequenceNr;
    }

    private function getInputSequenceNr() {
        if ($this->inputSequenceNr == NULL)
            throw new Exception("Eingabe-Sequenznummer nicht gesetzt!");
        else
            return str_pad($this->inputSequenceNr, 5, '0', STR_PAD_LEFT);
    }

    private function getTransactionType() {
        return $this->type;
    }

    private function getPaymentType() {
        return '0';
    }

    private function getProcessingFlag() {
        return '0';
    }

// Ende der Header Funktionen    

    public function setDtaId($dtaId) {
        if (!(strlen($dtaId) == 5))
            throw new Exception("Übergebene DTA-ID hat nicht 5 stellen!");
        else
            $this->dtaId = $dtaId;
    }

    private function getDtaId() {
        if ($this->dtaId == NULL)
            throw new Exception("DTA-ID nicht gesetzt!");
        else
            return $this->dtaId;
    }

    private function getTransactionId() {
        list($hash) = str_split(strtoupper(hash('md5', $this->dtaId)), 11);
        return $hash;
    }

    private function getReferenceNr() {
        return $this->getDtaId() . $this->getTransactionId();
    }

    public function setDebitAccount($debitAccount) {
        if (strlen($debitAccount) > 24)
            throw new Exeption("Übergebenes zu belastendes Konto zu lang!");
        else
            $this->debitAccount = str_pad($debitAccount, 24, $this->fillChar);
    }

    private function getDebitAccount() {
        if ($this->debitAccount == NULL)
            throw new Exception("Zu belastendes Konto nicht gesetzt!");
        else {
            if (strlen($this->debitAccount) != 24)
                throw new Exception("Gesetztes zu belastendes Konto hat ungültige Länge!");
            else
                return $this->debitAccount;
        }
    }

    public function setPaymentAmount($amount, $currencyCode, $valuta = NULL) {
        $paymentAmount = '';

        // Überprüfen des Valuta
        if ($valuta == NULL)
            $valuta = '      ';
        else {
            if ((!is_numeric($valuta)) ||  (!(strlen($valuta) == 6)))
                throw new Exception("Valuta muss ein Datum im Format JJMMTT sein!");
        }

        // Überprüfen des Betrages
        if (!((is_float($amount)) || (is_integer($amount))))
            throw new Exception("Der übergebene Betrag muss Eine Zahl sein!");
        else {
            $this->paymentAmountNumeric = $amount;
            $amount = str_pad(number_format($amount, 2, ',', ''), 12, $this->fillChar);
        }


        // Überprüfen des Währungscodes
        if (!$this->isIsoCurrencyCode($currencyCode))
            throw new Exception("Übergebener ISO-Währungscode nicht bekannt!");

        $paymentAmount = $valuta . $currencyCode . $amount;
        if (strlen($paymentAmount) != (6 + 3 + 12 ))
            throw new Exception("Zu setzender Vergütungsbetrag hat ungültige Länge!");
        else
            $this->paymentAmount = $paymentAmount;
    }

    /**
     * Gibt den gesetzen Vergütungsbetrag der Transaktion als numerischen Wert 
     * wieder.
     * 
     * @return float        Vergütungsbetrag als numerischer Wert
     * @throws Exception    Vergütungsbetrag nicht gesetzt
     */
    public function getPaymentAmountNumeric() {
        if ($this->paymentAmountNumeric == NULL)
            throw new Exception("Vergütungsbetrag nicht gesetzt!");
        else
            return $this->paymentAmount;
    }

    private function getPaymentAmount() {
        if ($this->paymentAmount == NULL)
            throw new Exception("Vergütungsbetrag nicht gesetzt!");
        else {
            if (strlen($this->paymentAmount) != (6 + 3 + 12))
                throw new Exception("Gesetzter Vergütungsbetrag hat ungültige Länge!");
            else
                return $this->paymentAmount;
        }
    }

    public function setClient($line1, $line2, $line3, $line4) {
        $client = array();
        array_push($client, str_pad(strtoupper($this->replaceUmlauts($line4)), 24, $this->fillChar));
        array_push($client, str_pad(strtoupper($this->replaceUmlauts($line3)), 24, $this->fillChar));
        array_push($client, str_pad(strtoupper($this->replaceUmlauts($line2)), 24, $this->fillChar));
        array_push($client, str_pad(strtoupper($this->replaceUmlauts($line1)), 24, $this->fillChar));
        $this->client = $client;
    }

    private function getClient() {
        if ($this->client == NULL)
            throw new Exception("Auftraggeber nicht gesetzt!");
        else {
            $clients = $this->client;
            $client = '';
            while ($line = array_pop($clients)) {
                $client .= $line;
            }
            return $client;
        }
    }

    public function setRecipient($account, $line1, $line2, $line3, $line4) {
        $recipient = array();
        array_push($recipient, str_pad(strtoupper($this->replaceUmlauts(substr($line4, 0, 24))), 24, $this->fillChar));
        array_push($recipient, str_pad(strtoupper($this->replaceUmlauts(substr($line3, 0, 24))), 24, $this->fillChar));
        array_push($recipient, str_pad(strtoupper($this->replaceUmlauts(substr($line2, 0, 24))), 24, $this->fillChar));
        array_push($recipient, str_pad(strtoupper($this->replaceUmlauts(substr($line1, 0, 24))), 24, $this->fillChar));
        array_push($recipient, str_pad(strtoupper('/C/' . $account), 30, $this->fillChar));
        $this->recipient = $recipient;
    }

    private function getRecipient() {
        if ($this->recipient == NULL)
            throw new Exception("Begünstigter nicht gesetzt!");
        else {
            $recipients = $this->recipient;
            $recipient = '';
            while ($line = array_pop($recipients)) {
                $recipient .= $line;
            }
            return $recipient;
        }
    }

    public function setPaymentReason($line1, $line2 = '', $line3 = '', $line4 = '') {
        $reason = array();
        array_push($reason, str_pad(strtoupper($this->replaceUmlauts($line4)), 28, $this->fillChar));
        array_push($reason, str_pad(strtoupper($this->replaceUmlauts($line3)), 28, $this->fillChar));
        array_push($reason, str_pad(strtoupper($this->replaceUmlauts($line2)), 28, $this->fillChar));
        array_push($reason, str_pad(strtoupper($this->replaceUmlauts($line1)), 28, $this->fillChar));
        $this->paymentReason = $reason;
    }

    private function getPaymentReason() {
        if ($this->paymentReason == NULL)
            return $this->getReserve(28)
                    . $this->getReserve(28)
                    . $this->getReserve(28)
                    . $this->getReserve(28);
        else {
            $reasons = $this->paymentReason;
            $reason = '';
            while ($line = array_pop($reasons)) {
                $reason .= $line;
            }
            return $reason;
        }
    }

    private function getEndRecipient() {
        return $this->getReserve(30)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24)
                . $this->getReserve(24);
    }

    private function replaceUmlauts($string) {
        $transMatrix = array(
            "  " => " ",
            "  " => " ",
            " " => " ",
            "À" => "A",
            "à" => "a",
            "Á" => "A",
            "á" => "a",
            "Â" => "A",
            "â" => "a",
            "Ã" => "A",
            "ã" => "a",
            "Ä" => "A",
            "ä" => "a",
            "Å" => "A",
            "å" => "a",
            "Æ" => "A",
            "æ" => "a",
            "Ç" => "C",
            "ç" => "c",
            "È" => "E",
            "è" => "e",
            "É" => "E",
            "é" => "e",
            "Ê" => "E",
            "ê" => "e",
            "Ë" => "E",
            "ë" => "e",
            "Ì" => "I",
            "ì" => "i",
            "Í" => "I",
            "í" => "i",
            "Î" => "I",
            "î" => "i",
            "Ï" => "I",
            "ï" => "i",
            "Ñ" => "N",
            "ñ" => "n",
            "Ò" => "O",
            "ò" => "o",
            "Ó" => "O",
            "ó" => "o",
            "Ô" => "O",
            "ô" => "o",
            "Õ" => "O",
            "õ" => "o",
            "Ö" => "O",
            "ö" => "o",
            "Ø" => "O",
            "ø" => "o",
            "Ù" => "U",
            "ù" => "u",
            "Ú" => "U",
            "ú" => "u",
            "Û" => "U",
            "û" => "u",
            "Ü" => "U",
            "ü" => "u",
            "Y´" => "Y",
            "y´" => "y",
            "ß" => "s"
        );
        return strtr($string, $transMatrix);
    }

}

?>
