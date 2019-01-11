<?php
namespace app\models\remit;

interface CapitalInterface
{   
    const PEANUT = 1;
    const JF = 2;
    const XIAONUO = 5;
    const WEISM = 6;
    const CUNGUAN = 10;
    const OTHER = 11;
    
    /**
     * Undocumented function
     *`
     * @param [type] $oRemit
     * @return [status,res_code,res_msg]
     */
    public function pay($oRemit);

    /**
     * Undocumented function
     *
     * @return void
     */
    public function hitRule();

   /**
    * Undocumented function
    *
    * @param [type] $oLoan
    * @return boolean
    */
    public function isSupport($oLoan);

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getFails();

}
