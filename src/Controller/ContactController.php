<?php
/**
 * ContactController.php - Main Controller
 *
 * Main Controller for Basket Contact Plugin
 *
 * @category Controller
 * @package Basket\Contact
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Basket\Contact\Controller;

use Application\Controller\CoreEntityController;
use Application\Model\CoreEntityModel;
use OnePlace\Basket\Model\BasketTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use OnePlace\Contact\Model\ContactTable;

class ContactController extends CoreEntityController {
    /**
     * Contact Table Object
     *
     * @since 1.0.0
     */
    protected $oTableGateway;

    /**
     * ContactController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param BasketTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, ContactTable $oTableGateway, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'basketcontact-single';
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);

        if ($oTableGateway) {
            # Attach TableGateway to Entity Models
            if (!isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    public function attachContact($oBasket) {
        $oContact = false;
        if($oBasket->contact_idfs != 0) {
            try {
                $oContactTbl = CoreEntityController::$oServiceManager->get(ContactTable::class);
            } catch(\RuntimeException $e) {

            }

            if(isset($oContactTbl)) {
                $oContact = $oContactTbl->getSingle($oBasket->contact_idfs);
                try {
                    $oAddressTbl = CoreEntityController::$oServiceManager->get(\OnePlace\Contact\Address\Model\AddressTable::class);
                } catch(\RuntimeException $e) {

                }
                if(isset($oAddressTbl)) {
                    $oAddresses = $oAddressTbl->fetchAll(false,['contact_idfs'=>$oContact->getID()]);
                    $oContact->oAddresses = $oAddresses;
                }
            }
        }

        $aFields = [];
        $aUserFields = CoreEntityController::$oSession->oUser->getMyFormFields();
        if(array_key_exists('contact-single',$aUserFields)) {
            $aFieldsTmp = $aUserFields['contact-single'];
            if(count($aFieldsTmp) > 0) {
                # add all contact-base fields
                foreach($aFieldsTmp as $oField) {
                    if($oField->tab == 'contact-base') {
                        $aFields[] = $oField;
                    }
                }
            }
        }

        # Pass Data to View - which will pass it to our partial
        return [
            # must be named aPartialExtraData
            'aPartialExtraData' => [
                # must be name of your partial
                'basket_contact'=> [
                    'oContact'=>$oContact,
                    'aFields'=>$aFields,
                ]
            ]
        ];
    }
}