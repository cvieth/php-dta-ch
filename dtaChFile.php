<?php

/**
 * @author Christoph Vieth <christoph.vieth@coreweb.de>
 */
require_once('dtaChTransaction.php');

class dtaChFile {

    private $transactions = array();
    private $transactionCounter = 0;
    private $creationDate;
    private $ident;
    public $currentTransaction = NULL;

    public function __construct($ident) {
        $this->creationDate = date('ymd');
        $this->ident = $ident;
    }

    public function addTransaction($type) {
        $this->transactionCounter++;
        $seqNr = $this->transactionCounter;
        $this->transactions[$seqNr] = new dtaChTransaction($seqNr, $type, $this->creationDate, $this->ident);
        return $seqNr;
    }

    public function loadTransaction($seqNr) {
        return $this->currentTransaction = $this->transactions[$seqNr];
    }

    public function saveTransaction($seqNr) {
        $result = $this->transactions[$seqNr] = $this->currentTransaction;
        if ($result)
            $this->currentTransaction = NULL;
        else
            return FALSE;
        return TRUE;
    }

    private function createTotalRecord() {
        $sum = 0;
        foreach ($this->transactions as $transaction) {
            $sum = $sum + $transaction->getAmount();
        }
        $id = $this->addTransaction(890);
        $totalRecord = $this->transactions[$id];
        $sum = 99.00;
        $totalRecord->setAmountSum($sum);
    }

    public function createFile($filename) {
        //$this->createTotalRecord();
        $fptr = fopen($filename, 'w+');
        if (!$fptr)
            throw new Exception('Kann Datei "' . $filename . '"nicht öffnen!');
        foreach ($this->transactions as $transaction) {
            fwrite($fptr, $transaction->toString());
        }
        fclose($fptr);
    }

}

?>
