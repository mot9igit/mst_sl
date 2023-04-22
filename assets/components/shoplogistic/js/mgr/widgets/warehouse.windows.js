Ext.namespace('shopLogistic.functions');

shopLogistic.functions.codeGen = function (codeGenCmp, codeCmp) {
    var value = codeGenCmp.getValue();
    var newCode = shopLogistic.utils.genRegExpString(value);

    codeCmp.setValue(newCode);
}

shopLogistic.window.CreateWarehouse = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_warehouse_create'),
        width: 900,
        baseParams: {
            action: 'mgr/warehouse/create',
        },
    });
    shopLogistic.window.CreateWarehouse.superclass.constructor.call(this, config);
};

Ext.extend(shopLogistic.window.CreateWarehouse, shopLogistic.window.Default, {
    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehouse_create'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouse.prototype.getFormFields.call(this, config),
            }]
        }]
    },
    getFormFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
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
            ]}, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_store_apikey'),
            name: 'apikey',
            id: config.id + '-apikey',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_warehouse_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_warehouse_delivery_tk'),
            name: 'delivery_tk',
            id: config.id + '-delivery_tk',
            checked: true,
        }, {
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_warehouse_website'),
                    name: 'website',
                    id: config.id + '-website',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_btx24_id'),
                    emptyText: _('shoplogistic_btx24_id_placeholder'),
                    name: 'btx24_id',
                    id: config.id + '-btx24_id',
                    anchor: '99%',
                    allowBlank: true,
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'shoplogistic-combo-city',
                    fieldLabel: _('shoplogistic_warehouse_city'),
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
                    fieldLabel: _('shoplogistic_warehouse_company_type'),
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
                    fieldLabel: _('shoplogistic_warehouse_ur_name'),
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
                    fieldLabel: _('shoplogistic_warehouse_inn'),
                    name: 'inn',
                    id: config.id + '-inn',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_warehouse_bank_knumber'),
                    name: 'bank_knumber',
                    id: config.id + '-bank_knumber',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_warehouse_bank_name'),
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
                    fieldLabel: _('shoplogistic_warehouse_bank_number'),
                    name: 'bank_number',
                    id: config.id + '-bank_number',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_warehouse_bank_bik'),
                    name: 'bank_bik',
                    id: config.id + '-bank_bik',
                    anchor: '99%'
                },{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_warehouse_unique_id'),
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
                    fieldLabel: _('shoplogistic_warehouse_address'),
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
                    fieldLabel: _('shoplogistic_warehouse_ur_address'),
                    name: 'ur_address',
                    id: config.id + '-ur_address',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_contact'),
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
                    fieldLabel: _('shoplogistic_warehouse_email'),
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
                    fieldLabel: _('shoplogistic_warehouse_phone'),
                    name: 'phone',
                    id: config.id + '-phone',
                    anchor: '99%'
                }]
            }]
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_file'),
            name: 'file',
            id: config.id + '-file',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_coordinats'),
            name: 'coordinats',
            id: config.id + '-coordinats',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_lat'),
            name: 'lat',
            id: config.id + '-lat',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_warehouse_lng'),
            name: 'lng',
            id: config.id + '-lng',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_warehouse_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-warehouse-window-create', shopLogistic.window.CreateWarehouse);

shopLogistic.window.UpdateWarehouse = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        baseParams: {
            title: _('shoplogistic_warehouse_update'),
            width: 900,
            action: 'mgr/warehouse/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateWarehouse.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateWarehouse, shopLogistic.window.CreateWarehouse, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_warehouse_update'),
                layout: 'form',
                items: shopLogistic.window.CreateWarehouse.prototype.getFormFields.call(this, config),
            }, {
                title: _('shoplogistic_warehouseusers'),
                items: [{
                    xtype: 'shoplogistic-grid-warehouseusers',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_warehouseremains'),
                items: [{
                    xtype: 'shoplogistic-grid-warehouseremains',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_warehousestores'),
                items: [{
                    xtype: 'shoplogistic-grid-warehousestores',
                    record: config.record,
                }]
            }, {
                title: _('shoplogistic_docs'),
                items: [{
                    xtype: 'shoplogistic-grid-warehouse-docs',
                    record: config.record,
                }]
            }]
        }];
    }

});
Ext.reg('shoplogistic-warehouse-window-update', shopLogistic.window.UpdateWarehouse);
