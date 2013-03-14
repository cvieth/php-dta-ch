<?php

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

}

?>
