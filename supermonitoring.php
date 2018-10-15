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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Supermonitoring extends Module
{
    protected $admin_tab = array();
    protected $html = '';

    public function __construct()
    {
        $this->name = 'supermonitoring';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'SITEIMPULSE';
        $this->module_key = '8a2d8ef33f5363c926e702c552739c36';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Super Monitoring');
        $this->description = $this->l('Monitor your online store uptime with www.supermonitoring.com services
         - and have all the charts and tables displayed in your PrestaShop panel.');

//        $this->ps_versions_compliancy = array('min' => '1.5.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        Configuration::updateValue('SUPERMONITORING_TOKEN', '');
        $installed = true;
        $this->initTabEnv();
        foreach ($this->admin_tab as $tab) {
            $installed &= $this->installTab($tab['classname'], $tab['parent'], $tab['displayname']);
        }

        return parent::install() &&
            $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SUPERMONITORING_TOKEN');
        $this->initTabEnv();
        foreach ($this->admin_tab as $tab) {
            $this->unInstallTab($tab['classname']);
        }
        return parent::uninstall();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitSupermonitoringModule')) == true) {
            $token = Tools::getValue('SUPERMONITORING_TOKEN');
            $isCorrect = $this->getApiResponse($token);
            if (!$token || $isCorrect == '0') {
                $this->html .= sprintf(
                    $this->displayError($this->l('Invalid token. You can obtain your token in "Your Account" 
                    section at %swww.supermonitoring.com%s.')),
                    '<a href="'.$this->getDomain().
                    '?utm_source=Presta&utm_medium=text&utm_campaign=plugin" target=\"_blank\">',
                    '</a>'
                );
            } else {
                $data = array();
                list($return, $link) = explode('|', $isCorrect);
                $data["token"] = $token;
                $data["language"] = Context::getContext()->language->iso_code;
                $data["version"] = '';
                $data["link"] = $link;
                unset($return);
                Configuration::updateValue('SUPERMONITORING_DATAS', serialize($data));
                $this->postProcess();
                $this->html .= $this->displayConfirmation($this->l('Changes have been saved'));
            }
        }
        $this->html .= $this->renderForm();

        return $this->html;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSupermonitoringModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'desc' => sprintf(
                            $this->l('You can obtain your token in "Your Account" 
                            section at %swww.supermonitoring.com%s.'),
                            '<a href="'.$this->getDomain().
                            '?utm_source=Presta&utm_medium=text&utm_campaign=plugin" target="_blank">',
                            '</a>'
                        ),
                        'name' => 'SUPERMONITORING_TOKEN',
                        'label' => $this->l('Token'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'SUPERMONITORING_TOKEN' => Configuration::get('SUPERMONITORING_TOKEN'),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }
    
    public function installTab($classname = null, $parent = null, $tab_name = null)
    {
        if (!$classname) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = $classname;
        if ($parent) {
            if (!is_int($parent)) {
                $tab->id_parent = (int) Tab::getIdFromClassName($parent);
            } else {
                $tab->id_parent = (int) $parent;
            }
        }
        $tab->module = $this->name;
        if (version_compare(_PS_VERSION_, '1.7.2') && $parent == 'SELL') {
            $tab->icon = 'announcement';
        }
        $tab->active = true;
        foreach (Language::getLanguages(true) as $lang) {
            switch ($lang['iso_code']) {
                case 'es':
                    $tab->name[$lang['id_lang']] = $tab_name['es'];
                    break;
                case 'co':
                    $tab->name[$lang['id_lang']] = $tab_name['en'];
                    break;
                case 'cb':
                    $tab->name[$lang['id_lang']] = $tab_name['en'];
                    break;
                case 'de':
                    $tab->name[$lang['id_lang']] = $tab_name['de'];
                    break;
                case 'pl':
                    $tab->name[$lang['id_lang']] = $tab_name['pl'];
                    break;
                default:
                    $tab->name[$lang['id_lang']] = $tab_name['en'];
                    break;
            }
        }

        if (!$tab->add()) {
            return false;
        }

        return true;
    }

    protected function unInstallTab($classname = false)
    {
        if (!$classname) {
            return true;
        }

        $idTab = Tab::getIdFromClassName($classname);
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }
    
    protected function initTabEnv()
    {
        $this->admin_tab[] = array(
            'classname' => 'AdminSMonitoring',
            'parent' => 'SELL',
            'displayname' => array (
                'en' => 'Super Monitoring',
                'es' => 'Super Monitoring',
                'pl' => 'Super Monitoring',
                'de' => 'Super Monitoring',
            )
        );
        
        $this->admin_tab[] = array(
            'classname' => 'AdminSMChecks',
            'parent' => 'AdminSMonitoring',
            'displayname' => array (
                'en' => 'Your Checks',
                'es' => 'Sus sitios web',
                'pl' => 'Twoje testy',
                'de' => 'Ihre Tests',
            )
        );
        
        $this->admin_tab[] = array(
            'classname' => 'AdminSMAccount',
            'parent' => 'AdminSMonitoring',
            'displayname' => array (
                'en' => 'Your Account',
                'es' => 'Su suenta',
                'pl' => 'Twoje konto',
                'de' => 'Ihr Konto',
            )
        );
        
        $this->admin_tab[] = array(
            'classname' => 'AdminSMContacts',
            'parent' => 'AdminSMonitoring',
            'displayname' => array (
                'en' => 'Your Contacts',
                'es' => 'Sus contactos',
                'pl' => 'Twoje kontakty',
                'de' => 'Ihre Kontakte',
            )
        );
    }
    
    public function getApiResponse($token)
    {
        $api = $this->getDomain() . 'API/';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        $string = 'f=wp_token&token=' . $token;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $string);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    
    public function getDomain()
    {
        $lang = Context::getContext()->language->iso_code;
        switch ($lang) {
            case 'es':
                $domain = 'https://www.supermonitoring.es/';
                break;
            case 'pl':
                $domain = 'https://www.supermonitoring.pl/';
                break;
            case 'de':
                $domain = 'https://www.supermonitoring.de/';
                break;
            default:
                $domain = 'https://www.supermonitoring.com/';
                break;
        }
        
        return $domain;
    }
}
