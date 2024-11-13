Ext.namespace('shopLogistic.functions');

shopLogistic.functions.codeGen = function (codeGenCmp, codeCmp) {
    var value = codeGenCmp.getValue();
    var newCode = shopLogistic.utils.genRegExpString(value);

    codeCmp.setValue(newCode);
}

shopLogistic.window.CreateStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_store_create'),
        width: 900,
        baseParams: {
            action: 'mgr/store/create',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.CreateStore.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateStore, shopLogistic.window.Default, {
    getFields: function (config) {
        console.log(config)
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_store_create'),
                layout: 'form',
                items: shopLogistic.window.CreateStore.prototype.getFormFields.call(this, config),
            }]
        }]
    },
    getFormFields: function (config) {
        // console.log(config)
        var default_fields = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        },{
            xtype: 'hidden',
            name: 'type',
            id: config.id + '-type'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_name_short'),
            name: 'name_short',
            id: config.id + '-name_short',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'dart-image-field',
            fieldLabel: _('shoplogistic_store_image'),
            description: "Отображается в списке складов. Необходимо для кастомизации.",
            name: 'image',
            id: config.id + '-image',
            anchor: '99%',
            allowBlank: true,
        },{
            cls: 'def-panel-group',
            layout: 'column',
            style: {marginTop: '10px', marginRight: '5px', background: '#eeeeee', padding: '10px 10px'},
            items: [{
                columnWidth: .75,
                layout: 'form',
                style: {marginTop: '-10px', marginRight: '5px'},
                items: [{
                    xtype: 'textfield',
                    name: 'apikey_gen',
                    id: config.id + '-apikey-gen',
                    hideLabel: true,
                    anchor: '100%',
                    originalValue: shopLogistic.config['regexp_gen_code'],
                    //allowBlank: false,
                }]
            }, {
                columnWidth: .25,
                layout: 'form',
                style: {marginTop: '0', marginLeft: '5px'},
                items: [{
                    xtype: 'button',
                    id: config.id + '-apikey-gen-btn',
                    hideLabel: true,
                    text: _('shoplogistic_apikey_gen_btn'),
                    cls: 'sl-btn-primary3',
                    anchor: '100%',
                    style: 'padding:5px 5px 7px;',
                    listeners: {
                        click: {
                            fn: function () {
                                var codeGenCmp = Ext.getCmp(config.id + '-apikey-gen');
                                var codeCmp = Ext.getCmp(config.id + '-apikey');
                                shopLogistic.functions.codeGen(codeGenCmp, codeCmp)
                            },
                            scope: this
                        }
                    }
                }],
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_apikey'),
            description: "Можно сгенерировать воспользовавшись инструментом выше.",
            name: 'apikey',
            id: config.id + '-apikey',
            anchor: '99%',
            allowBlank: false,
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_btx24_id'),
            description: "Поле для идентификации организации в Bitrix24. Если не будет заполнено, то не придет поле Организации, при совершении заказа в маркетплейсе.",
            name: 'btx24_id',
            id: config.id + '-btx24_id',
            anchor: '99%',
            allowBlank: true,
        },{
            xtype: 'shoplogistic-store-integration',
            fieldLabel: _('shoplogistic_store_type_integration'),
            description: "От типа интеграции зависит каким образом будет осуществляться контроль за Складом и критерии его отключения.",
            name: 'type_integration',
            hiddenName: 'type_integration',
            id: config.id + '-type_integration',
            anchor: '99%',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_yml_file'),
            emptyText: _('shoplogistic_store_yml_file'),
            name: 'yml_file',
            id: config.id + '-yml_file',
            anchor: '99%',
            allowBlank: true,
        }, {
            xtype: 'shoplogistic-combo-vendor',
            fieldLabel: "Базовый бренд",
            description: "Если у склада представлен один бренд, то для четкости сопоставления Вы можете указать его как базовый для всех карточек товара этого склада.",
            name: 'base_vendor',
            anchor: '99%',
            id: config.id + '-base_vendor',
            allowBlank: true
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_active'),
            description: "Флаг активности склада",
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            title: 'Флаги слежки за API',
            html: "<div class='dart-alert dart-alert-info'>На основании этих параметров осуществляется контроль обмена информации и отключение Склада при потери связи.</div>",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_check_remains'),
                    description: "Флаг слежки за обменом остатков. Если отмечен, то осуществляется контроль обмена и отключение Склада при потери связи.",
                    name: 'check_remains',
                    id: config.id + '-check_remains',
                    anchor: '99%',
                    checked: true
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_check_docs'),
                    description: "Флаг слежки за обменом остатков. Если отмечен, то осуществляется контроль обмена и отключение Склада при потери связи.",
                    name: 'check_docs',
                    id: config.id + '-check_docs',
                    anchor: '99%',
                    checked: true,
                }]
            }]
        }, {
            title: 'Даты обращения по API',
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xdatetime',
                    fieldLabel: _('shoplogistic_store_date_api_ping'),
                    name: 'date_api_ping',
                    id: config.id + '-date_api_ping',
                    anchor: '99%',
                    allowBlank: true
                },{
                    xtype: 'xdatetime',
                    fieldLabel: _('shoplogistic_store_date_remains_update'),
                    name: 'date_remains_update',
                    id: config.id + '-date_remains_update',
                    anchor: '99%',
                    allowBlank: true
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xdatetime',
                    fieldLabel: _('shoplogistic_store_date_docs_update'),
                    name: 'date_docs_update',
                    id: config.id + '-date_docs_update',
                    anchor: '99%',
                    allowBlank: true
                },{
                    xtype: 'textfield',
                    fieldLabel: "Версия модуля обмена",
                    description: "Версия модуля обмена в 1С",
                    name: 'version',
                    id: config.id + '-version',
                    anchor: '99%',
                    allowBlank: true
                }]
            }]
        },{
            title: 'Местоположение',
            html: "<div class='dart-alert dart-alert-info'>Параметры местоположения склада. Координаты пока указываем во все поля. Позже будет оптимизация.</div>",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_website'),
                    description: "Можно указать для уточнения возможности парсинга номенклатуры.",
                    name: 'website',
                    id: config.id + '-website',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_lat'),
                    description: "Широта",
                    name: 'lat',
                    id: config.id + '-lat',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_coordinats'),
                    description: "Координаты через запятую",
                    name: 'coordinats',
                    id: config.id + '-coordinats',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-combo-city',
                    fieldLabel: _('shoplogistic_store_city'),
                    description: "На базе данного параметра происходит расчет доставки в модулях 'Закупки' и 'Маркеплейс'",
                    name: 'city',
                    id: config.id + '-city',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_lng'),
                    description: "Долгота",
                    name: 'lng',
                    id: config.id + '-lng',
                    anchor: '99%'
                }]
            }]
        },{
            title: 'Участие в пространствах',
            html: "<div class='dart-alert dart-alert-info'>Обязательно указывайте, где склад должен быть доступен. В противном случае, даже при условии, что Склад включен, он не будет отображаться в пространствах.</div>",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_marketplace'),
                    description: "Розничный склад для модуля 'Маркетплейс'",
                    name: 'marketplace',
                    id: config.id + '-marketplace',
                    anchor: '99%',
                    checked: true
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_opt_marketplace'),
                    description: "Оптовый склад для модуля 'Закупки'",
                    name: 'opt_marketplace',
                    id: config.id + '-opt_marketplace',
                    anchor: '99%',
                    checked: false
                }]
            }]
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_address'),
            name: 'address',
            id: config.id + '-address',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_address_short'),
            name: 'address_short',
            id: config.id + '-address_short',
            anchor: '99%'
        }, {
            title: 'Контактное лицо',
            html: "<div class='dart-alert dart-alert-info'>Указывайте контакты ответственного за отгрузки с данного Склада.</div>",
            cls: 'def-panel-group',
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_contact'),
                    name: 'contact',
                    id: config.id + '-contact',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_email'),
                    name: 'email',
                    id: config.id + '-email',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }]
            }]
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_description'),
            description: "Описание для команды подключения",
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
        if(config.type == 2 || config.type == 3){
            default_fields.push({
                xtype: 'xcheckbox',
                boxLabel: _('shoplogistic_warehouse_delivery_tk'),
                name: 'delivery_tk',
                id: config.id + '-delivery_tk',
                checked: true,
            })
        }
        return default_fields
    },
});
Ext.reg('shoplogistic-store-window-create', shopLogistic.window.CreateStore);

shopLogistic.window.UpdateStore = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_store_update'),
            width: 900,
            action: 'mgr/store/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateStore.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStore, shopLogistic.window.CreateStore, {

    getFields: function (config) {
        if(config.store == 1){
            var title = _('shoplogistic_store_update');
        }
        if(config.warehouse == 1){
            var title = _('shoplogistic_warehouse_update');
        }
        if(config.vendor == 1){
            var title = _('shoplogistic_vendor_update');
        }
        var default_tabs = [{
            title: title,
            layout: 'form',
            items: shopLogistic.window.CreateStore.prototype.getFormFields.call(this, config),
        }, {
            title: _('shoplogistic_storeusers'),
            items: [{
                xtype: 'shoplogistic-grid-storeusers',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_store_settings'),
            items: [{
                xtype: 'shoplogistic-params-store-setting-grid',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_store_apirequest'),
            items: [{
                xtype: 'shoplogistic-store-apirequest-grid',
                record: config.record,
            }]
        }, {
            title: "Каталоги остатков",
            items: [{
                xtype: 'shoplogistic-grid-storeremains-cats',
                record: config.record,
            }]
        }, {
            title: _('shoplogistic_storeremains'),
            items: [{
                xtype: 'shoplogistic-grid-storeremains',
                record: config.record,
            }]
        }, {
            title: "Группы товаров",
            items: [{
                xtype: 'shoplogistic-grid-store-remains-groups',
                record: config.record,
            }]
        },{
            title: _('shoplogistic_docs'),
            items: [{
                xtype: 'shoplogistic-grid-stores-docs',
                record: config.record,
            }]
        }];
        if(config.store == 1){
            default_tabs.push({
                title: _('shoplogistic_vendorbrands'),
                items: [{
                    html: 'Укажите бренды, за которыми нужно следить',
                    cls: 'panel-desc'
                }, {
                    xtype: 'shoplogistic-grid-vendorbrands',
                    record: config.record,
                }]
            })
        }
        if(config.warehouse == 1){
            default_tabs.push({
                title: _('shoplogistic_warehousestores'),
                items: [{
                    xtype: 'shoplogistic-grid-warehousestores',
                    record: config.record,
                }]
            })
        }
        if(config.vendor == 1){
            default_tabs.push({
                title: _('shoplogistic_vendorbrands'),
                items: [{
                    xtype: 'shoplogistic-grid-vendorbrands',
                    record: config.record,
                }]
            },{
                title: _('shoplogistic_matrixs'),
                items: [{
                    xtype: 'shoplogistic-grid-matrix',
                    record: config.record,
                }]
            });
        }
        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-store-window-update', shopLogistic.window.UpdateStore);
