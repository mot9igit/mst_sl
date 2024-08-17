{var $style = [
'logo' => 'display:block;margin: auto;',
'a' => 'color:#348eda;',
'p' => 'font-family: Arial;color: #666666;font-size: 14px;margin: 0 20px 10px;',
'h' => 'font-family:Arial;color: #111111;font-weight: 200;line-height: 1.2em;margin: 40px 20px;',
'h1' => 'font-size: 36px;',
'h2' => 'font-size: 28px;',
'h3' => 'font-size: 22px;',
'h4' => 'font-size: 20px;',
'th' => 'font-family: Arial;text-align: left;color: #111111;',
'td' => 'font-family: Arial;text-align: left;color: #111111;',
]}
{var $site_url = ('site_url' | option) | preg_replace : '#/$#' : ''}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{$_modx->config.site_name}</title>
</head>
<body style="margin:0;padding:0;background:#f6f6f6;">
<div style="height:100%;padding-top:20px;background:#f6f6f6;">
    <a href="{$site_url}">
        <img style="{$style.logo}"
             src="{$site_url}{$_modx->getPlaceholder('+conf_mail_logo')}"
             alt="{$_modx->config.site_name}"
             width="240"/>
    </a>
    <!-- body -->
    <table class="body-wrap" style="padding:0 20px 20px 20px;width: 100%;background:#f6f6f6;margin-top:10px;">
        <tr>
            <td></td>
            <td class="container" style="border:1px solid #f0f0f0;background:#ffffff;width:800px;margin:auto;">
                <div class="content">
                    <table style="width:100%;">
                        <tr>
                            <td>
                                <h3 style="{$style.h}{$style.h3}">
                                    Сообщение с сайта {$_modx->config.site_name}.
                                </h3>
                                <div style="{$style.p}">
                                    <h4 style="margin-bottom: 10px;">Форма рекламации</h4>
                                    <p style="{$style.p}">
                                        <strong>Дата:</strong> {$smarty.now|date_format:'%d.%m.%y'}<br/>
                                        {if $company}
                                        <strong>Компания/ИП:</strong> {$company}<br/>
                                        {/if}
                                        {if $productName}
                                        <strong>Название изделия:</strong> {$productName}<br/>
                                        {/if}
                                        {if $productNumber}
                                        <strong>Номер изделия:</strong> {$productNumber}<br/>
                                        {/if}
                                        {if $dateSale}
                                        <strong>Дата продажи:</strong> {$dateSale}<br/>
                                        {/if}
                                        {if $ower}
                                        <strong>Владелец:</strong> {$ower}<br/>
                                        {/if}
                                        {if $telOwner}
                                        <strong>Телефон:</strong> {$telOwner}<br/>
                                        {/if}
                                        {if $person}
                                        <strong>Контактное лицо:</strong> {$person}<br/>
                                        {/if}
                                        {if $telPerson}
                                        <strong>Телефон:</strong> {$telPerson}<br/>
                                        {/if}
                                        {if $description}
                                        <strong>Подробное описание неисправности:</strong> {$description}<br/>
                                        {/if}
                                        {if $taskList}
                                        <strong>Список необходимых задач:</strong> {$taskList}<br/>
                                        {/if}
                                        {if $image}
                                    <h4 style="margin-bottom: 6px;">Ссылки на фото/видео:</h4>
                                    {foreach $image as $key => $value}
                                        • <a href={$image[$key]}>{$image[$key]}</a><br/>
                                    {/foreach}
                                    <br/>
                                    {/if}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- /content -->
            </td>
            <td></td>
        </tr>
    </table>
    <!-- /body -->
    <!-- footer -->
    <table style="clear:both !important;width: 100%;">
        <tr>
            <td></td>
            <td class="container">
                <!-- content -->
                <div class="content">
                    <table style="width:100%;text-align: center;">
                        <tr>
                            <td align="center">
                                <p style="{$style.p}">
                                    <a href="{$site_url}" style="{$style.a}">
                                        {$_modx->config.site_name}
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- /content -->
            </td>
            <td></td>
        </tr>
    </table>
    <!-- /footer -->
</div>
</body>
</html>