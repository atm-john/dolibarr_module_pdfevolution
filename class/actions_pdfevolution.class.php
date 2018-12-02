<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_pdfevolution.class.php
 * \ingroup pdfevolution
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionspdfevolution
 */
class Actionspdfevolution
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/*
     * Overloading the defineColumnField function
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   PDF object      $pdfDoc         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
	public function defineColumnField($parameters, &$pdfDoc, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;
        
        
        $contexts = explode(':',$parameters['context']);
        if ($pdfDoc->name == 'sponge' ||  $pdfDoc->name == 'sponge_btp' || $pdfDoc->name == 'eratosthene' || $pdfDoc->name == 'cyan' ){
            
            // Translations
            $langs->loadLangs(array("pdfevolution@pdfevolution"));

            $def = array(
                'rank' => 55,
                'width' => 20, // in mm
                'status' => false,
                'title' => array(
                    'label' => $langs->trans('UnitPriceAfterDiscount')
                ),
                'border-left' => true, // add left line separator
            );
            
            if ($pdfDoc->atleastonediscount && !empty($conf->global->PDFEVOLUTION_ADD_UNIT_PRICE_AFTER_DISCOUNT)){
                $def['status'] = true;
            }
            
            $pdfDoc->insertNewColumnDef('UnitPriceAfterDiscount', $def, 'discount',1);
            
            
/*
            $def = array(
                'rank' => 55,
                'width' => 50, // in mm
                'status' => true,
                'title' => array(
                    'label' => 'Ref'
                ),
                'border-left' => true, // add left line separator
            );
            $pdfDoc->cols['desc']['border-left'] = true;
            $pdfDoc->insertNewColumnDef('Ref', $def, 'desc',0);
            */

            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_TOTALEXCLTAX)){
                $pdfDoc->cols['totalexcltax']['status'] = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_DISCOUNT)){
                $pdfDoc->cols['discount']['status']     = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_UNIT)){
                $pdfDoc->cols['unit']['status']         = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_PROGRESS)){
                $pdfDoc->cols['progress']['status']     = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_QTY)){
                $pdfDoc->cols['qty']['status']          = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_SUBPRICE)){
                $pdfDoc->cols['subprice']['status']     = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_VAT)){
                $pdfDoc->cols['vat']['status']          = false;
            }
            
            if(!empty($conf->global->PDFEVOLUTION_DISABLE_COL_PHOTO)){
                $pdfDoc->cols['photo']['status']        = false;
            }
            
            
            $Tcol = array(
                'TOTALEXCLTAX', 'DISCOUNT', 'UNIT_PRICE_AFTER_DISCOUNT', 'UNIT', 'PROGRESS', 'QTY', 'SUBPRICE', 'VAT', 'PHOTO'
            ); 
            
            foreach ($Tcol as $col){
                $constUsed = 'PDFEVOLUTION_DISABLE_LEFT_SEP_'.$col;
                if(!empty($conf->global->{$constUsed})){
                    $pdfDoc->cols[strtolower($col)]['border-left']        = false;
                }
            }
            
            

            
            
           // var_dump($extrafields);
            // Load attribute_label
            $extrafields = new ExtraFields($db);
            $extrafields->fetch_name_optionals_label($parameters['object']->lines[0]->element);
            if(!empty($extrafields->attribute_pos))
            {
                $lastCol = 'photo';
                foreach($extrafields->attribute_pos as $key => $pos)
                {
                    $newCol = 'attribute_'.$key;
                    
                    
                    $def = array(
                        'width' => strlen($extrafields->attribute_label[$key])*2.5, // in mm
                        'status' => false,
                        'title' => array(
                            'label' => $extrafields->attribute_label[$key]
                        ),
                        'border-left' => true, // add left line separator
                    );
                    
                    //exit;
                    
                    if ($extrafields->attribute_list[$key] == 4){
                        $def['status'] = true;
                    }
                    
                    $pdfDoc->insertNewColumnDef( $newCol , $def, $lastCol,0);
                    
                    /*
                    $extrafields->attribute_pos[$tab->name];
                    $extrafields->attribute_type[$tab->name]=$tab->type;
                    $extrafields->attribute_label[$tab->name]=$tab->label;
                    $extrafields->attribute_entityid[$tab->name]=$tab->entity;
                    $extrafields->attribute_param[$tab->name];*/
                    $lastCol = $newCol;
                }
                
            }
            
        }
        
        
        
        
    }
    
    /*
     * Overloading the printPDFline function
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function printPDFline($parameters, &$pdfDoc, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;
        $pdf =& $parameters['pdf'];
        $i = $parameters['i'];
        $outputlangs = $parameters['outputlangs'];
        
        if ($pdfDoc->getColumnStatus('UnitPriceAfterDiscount'))
        {
            $object = $parameters['object'];
            
            
            $sign=1;
            if (isset($object->type) && $object->type == 2 && ! empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE)) $sign=-1;
            
            $subprice = ($conf->multicurrency->enabled && $object->multicurrency_tx != 1 ? $object->lines[$i]->multicurrency_subprice : $object->lines[$i]->subprice);
            $subprice = $sign * $subprice;
            
            $celText = '';
            if ($object->lines[$i]->special_code == 3){
                $celText = '';
            }
            elseif(!empty($object->lines[$i]->remise_percent)){
                $subpriceWD = $subprice - ($subprice * $object->lines[$i]->remise_percent / 100) ;
                $celText = price($subpriceWD, 0, $outputlangs);
            }
            
            
            if(!empty($celText)){
                $pdfDoc->printStdColumnContent($pdf, $parameters['curY'], 'UnitPriceAfterDiscount', $celText );
                $parameters['nexY'] = max($pdf->GetY(),$parameters['nexY']);
            }
        }

        

        if ($pdfDoc->getColumnStatus('Ref'))
        {
            $object = $parameters['object'];
            
            if(!empty($object->lines[$i]->ref)){
                $pdfDoc->printStdColumnContent($pdf, $parameters['curY'], 'Ref', $object->lines[$i]->ref );
                $parameters['nexY'] = max($pdf->GetY(),$parameters['nexY']);
            }

        }
        //var_dump($parameters['object']->lines[0]->array_options);exit;
        
        $extrafields = new ExtraFields($db);
        $extrafields->fetch_name_optionals_label($parameters['object']->lines[$i]->element);
        if(!empty($extrafields->attribute_pos) && !empty($parameters['object']->lines[$i]->array_options))
        {
            foreach($extrafields->attribute_pos as $key => $pos)
            {
                $newCol = 'attribute_'.$key;
                $extrafieldKey = 'options_'.$key;
                if ($pdfDoc->getColumnStatus($newCol) && !empty($parameters['object']->lines[$i]->array_options[$extrafieldKey]))
                {
                    $pdfDoc->printStdColumnContent($pdf, $parameters['curY'], $newCol, $parameters['object']->lines[$i]->array_options[$extrafieldKey] );
                    $parameters['nexY'] = max($pdf->GetY(),$parameters['nexY']);
                }
            }
        }
        return 1;
    }
}