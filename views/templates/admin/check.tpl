{*
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
*}

<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Super Monitoring' mod='supermonitoring'}</h3>
    {if isset($data) && isset($data.token) && $data.token}
    <iframe id="frame" width="100%" frameborder="0" src="{$sp_url|escape:'html':'UTF-8'}index.php?wp_token={$data.token|escape:'html':'UTF-8'}&cms=presta{$sp}"></iframe>
    <script type="text/javascript">
        function resizeIframe() {
            var height = document.documentElement.clientHeight;
            height -= document.getElementById('frame').offsetTop;

            // not sure how to get this dynamically
            height -= 40; /* whatever you set your body bottom margin/padding to be */

            document.getElementById('frame').style.height = height +"px";

        };
        document.getElementById('frame').onload = resizeIframe;
        window.onresize = resizeIframe;
    </script>
    {else}
    <p class="alert alert-warning">
        {l s='Super Monitoring configuration not available' mod='supermonitoring'}
    </p>
    {/if}
</div>
