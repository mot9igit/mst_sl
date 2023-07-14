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
        },{
            xtype: 'modx-combo-browser',
            fieldLabel: _('shoplogistic_store_image'),
            name: 'image',
            id: config.id + '-image',
            anchor: '99%',
            allowBlank: true,
        },{
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
            }
            ]},{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_apikey'),
            emptyText: _('shoplogistic_apikey_placeholder'),
            name: 'apikey',
            id: config.id + '-apikey',
            anchor: '99%',
            allowBlank: false,
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_btx24_id'),
            emptyText: _('shoplogistic_btx24_id_placeholder'),
            name: 'btx24_id',
            id: config.id + '-btx24_id',
            anchor: '99%',
            allowBlank: true,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_store_integration'),
            name: 'integration',
            id: config.id + '-integration',
            checked: false,
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_check_remains'),
                    name: 'check_remains',
                    id: config.id + '-check_remains',
                    anchor: '99%',
                    checked: true
                },{
                    xtype: 'xcheckbox',
                    boxLabel: _('shoplogistic_store_check_docs'),
                    name: 'check_docs',
                    id: config.id + '-check_docs',
                    anchor: '99%',
                    checked: true,
                }]
            },{
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
                },{
                    xtype: 'xdatetime',
                    fieldLabel: _('shoplogistic_store_date_docs_update'),
                    name: 'date_docs_update',
                    id: config.id + '-date_docs_update',
                    anchor: '99%',
                    allowBlank: true
                }]
            }]
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_website'),
                    name: 'website',
                    id: config.id + '-website',
                    anchor: '99%'
                },]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-combo-city',
                    fieldLabel: _('shoplogistic_store_city'),
                    name: 'city',
                    id: config.id + '-city',
                    anchor: '99%'
                }]
            }]
        },{
            title: 'Реквизиты',
            cls: 'def-panel',
            layout: 'column',
            items: [{
                columnWidth: .3,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'combo-company_type',
                    fieldLabel: _('shoplogistic_store_company_type'),
                    name: 'company_type',
                    id: config.id + '-company_type',
                    anchor: '99%'
                }]
            },{
                columnWidth: .7,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_ur_name'),
                    name: 'ur_name',
                    id: config.id + '-ur_name',
                    anchor: '99%',
                    allowBlank: false,
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                cls: 'no-margin',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_inn'),
                    name: 'inn',
                    id: config.id + '-inn',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_bank_knumber'),
                    name: 'bank_knumber',
                    id: config.id + '-bank_knumber',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_bank_name'),
                    name: 'bank_name',
                    id: config.id + '-bank_name',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_bank_number'),
                    name: 'bank_number',
                    id: config.id + '-bank_number',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_bank_bik'),
                    name: 'bank_bik',
                    id: config.id + '-bank_bik',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_store_unique_id'),
                    name: 'unique_id',
                    id: config.id + '-unique_id',
                    anchor: '99%'
                }]
            },{
                columnWidth: 1,
                cls: 'no-margin',
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textarea',
                    fieldLabel: _('shoplogistic_store_address'),
                    name: 'address',
                    id: config.id + '-address',
                    anchor: '99%'
                }]
            },{
                columnWidth: 1,
                cls: 'no-margin',
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textarea',
                    fieldLabel: _('shoplogistic_store_ur_address'),
                    name: 'ur_address',
                    id: config.id + '-ur_address',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_contact'),
            name: 'contact',
            id: config.id + '-contact',
            anchor: '99%'
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
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
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_coordinats'),
            name: 'coordinats',
            id: config.id + '-coordinats',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_lat'),
            name: 'lat',
            id: config.id + '-lat',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_lng'),
            name: 'lng',
            id: config.id + '-lng',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_store_description'),
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
        if(config.type == 1){
            var title = _('shoplogistic_store_update');
        }
        if(config.type == 2){
            var title = _('shoplogistic_warehouse_update');
        }
        if(config.type == 3){
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
        }];
        if(config.type == 1){
            default_tabs.push({
                title: _('shoplogistic_storeremains'),
                items: [{
                    xtype: 'shoplogistic-grid-storeremains',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storebalance'),
                items: [{
                    xtype: 'shoplogistic-grid-storebalance',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storeregistry'),
                items: [{
                    xtype: 'shoplogistic-grid-storeregistry',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_docs'),
                items: [{
                    xtype: 'shoplogistic-grid-stores-docs',
                    record: config.record,
                }]
            })
        }
        if(config.type == 2){
            default_tabs.push({
                title: _('shoplogistic_storeremains'),
                items: [{
                    xtype: 'shoplogistic-grid-storeremains',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_warehousestores'),
                items: [{
                    xtype: 'shoplogistic-grid-warehousestores',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storebalance'),
                items: [{
                    xtype: 'shoplogistic-grid-storebalance',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_storeregistry'),
                items: [{
                    xtype: 'shoplogistic-grid-storeregistry',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_docs'),
                items: [{
                    xtype: 'shoplogistic-grid-stores-docs',
                    record: config.record,
                }]
            })
        }
        if(config.type == 3){
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
