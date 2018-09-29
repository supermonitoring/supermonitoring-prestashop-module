<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminSMContactsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->lang = false;
        $this->explicitSelect = false;
        $this->bootstrap = true;
        $this->table = 'csm';
        $this->identifier = 'id_cms';
        $this->className = 'CMS';
        $this->allow_export = false;
        $this->delete = true;
        
        parent::__construct();

        return true;
    }
    
    public function renderList()
    {
        $data = Configuration::get('SUPERMONITORING_DATAS');
        $this->context->smarty->assign('data', @unserialize($data));
        $this->context->smarty->assign('sp', '&s=contacts');
        $this->context->smarty->assign('sp_url', $this->module->getDomain());
        return $this->context->smarty->fetch($this->module->getLocalPath().'views/templates/admin/check.tpl');
    }
    
    public function initToolbarTitle()
    {
        return '';
    }
}
