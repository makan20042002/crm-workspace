<?php
declare(strict_types=1);
const APP_VERSION = '10.1.3';
const APP_PRODUCT_NAME = 'CRM Workspace';
const APP_SESSION_NAME = 'CRMWORKSPACESESSID';
const APP_CREDIT = ['name'=>'Makan','site'=>'https://makanlab.tech'];
const APP_LICENSE = 'AGPL-3.0-or-later';
function app_credit_html(): string {return 'Powered by <a href="'.htmlspecialchars(APP_CREDIT['site'],ENT_QUOTES,'UTF-8').'" target="_blank" rel="noopener">'.htmlspecialchars(APP_CREDIT['name'],ENT_QUOTES,'UTF-8').' - '.htmlspecialchars(APP_CREDIT['site'],ENT_QUOTES,'UTF-8').'</a>';}
