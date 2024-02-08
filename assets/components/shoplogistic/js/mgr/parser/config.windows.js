shopLogistic.window.CreateConfig = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_config_create'),
        width: 900,
        baseParams: {
            action: 'mgr/parser/configs/create'
        },
    });
    shopLogistic.window.CreateConfig.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateConfig, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'config_id',
            id: config.id + '-config_id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_base_url'),
            name: 'base_url',
            id: config.id + '-base_url',
            anchor: '99%'
        },{
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_parser_config_categories_base'),
                    name: 'categories_base',
                    id: config.id + '-categories_base',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_parser_config_products_base'),
                    name: 'products_base',
                    id: config.id + '-products_base',
                    anchor: '99%'
                }]
            },{
                columnWidth: .5,
                layout: 'form',
                defaults: {msgTarget: 'under'},
                items: [{
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_parser_config_categories_base_inner'),
                    name: 'categories_base_inner',
                    id: config.id + '-categories_base_inner',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('shoplogistic_parser_config_products_base_inner'),
                    name: 'products_base_inner',
                    id: config.id + '-products_base_inner',
                    anchor: '99%'
                }]
            }]
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parser_config_unique'),
            name: 'unique',
            id: config.id + '-unique',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_key_product_field'),
            name: 'key_product_field',
            id: config.id + '-key_product_field',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parser_config_pagination'),
            name: 'pagination',
            id: config.id + '-pagination',
            anchor: '99%',
            listeners: {
                check: {
                    fn: function (checkbox) {
                        this.handlePaginationFields(checkbox);
                    }, scope: this
                },
                afterrender: {
                    fn: function (checkbox) {
                        this.handlePaginationFields(checkbox);
                    }, scope: this
                }
            },
        }, {
            cls: "pagination",
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_pagination_selector'),
            name: 'pagination_selector',
            id: config.id + '-pagination_selector',
            anchor: '99%'
        }, {
            cls: "pagination",
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_pagination_filters'),
            name: 'pagination_filters',
            id: config.id + '-pagination_filters',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parser_config_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    },

    handlePaginationFields: function (checkbox) {

        var selector = Ext.getCmp(this.config.id + '-pagination_selector');
        var filters = Ext.getCmp(this.config.id + '-pagination_filters');
        if (checkbox.checked) {
            selector.enable().show();
            filters.enable().show();
        } else {
            selector.hide().disable();
            filters.hide().disable();
        }
    },
});
Ext.reg('shoplogistic-window-parser-configs-create', shopLogistic.window.CreateConfig);


shopLogistic.window.UpdateConfig = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_config_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parser/configs/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateConfig.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateConfig, shopLogistic.window.CreateConfig, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            autoHeight: true,
            deferredRender: false,
            forceLayout: true,
            width: '98%',
            items: [{
                title: _('shoplogistic_parser_config_update'),
                layout: 'form',
                items: shopLogistic.window.CreateConfig.prototype.getFields.call(this, config)
            }, {
                title: _('shoplogistic_parser_config_fields_categories'),
                items: [{
                    html: 'Будьте внимательны при заполнений полей.',
                    cls: 'panel-desc'
                },{
                    xtype: 'shoplogistic-grid-parser-config-fields',
                    id: 'shoplogistic-grid-parser-config-fields__' + 1,
                    record: config.record,
                    type: 1,
                }]
            }, {
                title: _('shoplogistic_parser_config_fields_products'),
                items: [{
                    html: 'Будьте внимательны при заполнений полей.',
                    cls: 'panel-desc'
                },{
                    xtype: 'shoplogistic-grid-parser-config-fields',
                    id: 'shoplogistic-grid-parser-config-fields__' + 2,
                    record: config.record,
                    type: 2,
                }]
            }],
            listeners: {
                'tabchange': {fn: function(panel) {
                        panel.doLayout();
                    },
                    scope: this
                }
            }
        }]
    }

});
Ext.reg('shoplogistic-window-parser-configs-update', shopLogistic.window.UpdateConfig);