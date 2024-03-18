shopLogistic.window.CreateConfigField = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_config_fields_create'),
        width: 900,
        baseParams: {
            action: 'mgr/parser/fields/create'
        },
    });
    shopLogistic.window.CreateConfigField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateConfigField, shopLogistic.window.Default, {

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
            xtype: 'hidden',
            name: 'field_object',
            id: config.id + '-field_object',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_selector'),
            name: 'selector',
            id: config.id + '-selector',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parser_config_fields_this'),
            name: 'this',
            id: config.id + '-this',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_parser_config_fields_index_search'),
            name: 'index_search',
            id: config.id + '-index_search',
            anchor: '99%',
            listeners: {
                check: {
                    fn: function (checkbox) {
                        this.handleIndexFields(checkbox);
                    }, scope: this
                },
                afterrender: {
                    fn: function (checkbox) {
                        this.handleIndexFields(checkbox);
                    }, scope: this
                }
            },
        }, {
            cls: "index",
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_index'),
            name: 'index',
            id: config.id + '-index',
            anchor: '99%'
        }, {
            cls: "index",
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_subelement'),
            name: 'subelement',
            id: config.id + '-subelement',
            anchor: '99%'
        },{
            cls: "index",
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_subindex'),
            name: 'subindex',
            id: config.id + '-subindex',
            anchor: '99%'
        },{
            xtype: 'combo-parser_field_type',
            fieldLabel: _('shoplogistic_parser_config_fields_field_type'),
            name: 'field_type',
            hiddenName: 'field_type',
            id: config.id + '-field_type',
            anchor: '99%'
        },{
            xtype: 'combo-parser_field_source',
            fieldLabel: _('shoplogistic_parser_config_fields_type'),
            hiddenName: 'type',
            name: 'type',
            id: config.id + '-type',
            anchor: '99%',
            listeners: {
                select: {
                    fn: function (value) {
                        this.handleAttrFields(value);
                    }, scope: this
                },
                afterrender: {
                    fn: function (value) {
                        this.handleAttrFields(value);
                    }, scope: this
                }
            },
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_parser_config_fields_element_name'),
            name: 'element_name',
            id: config.id + '-element_name',
            anchor: '99%'
        },{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parser_config_fields_filters'),
            name: 'field_filters',
            id: config.id + '-field_filters',
            anchor: '99%'
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_parser_config_fields_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        }];
    },

    handleAttrFields: function (value) {
        var element_name = Ext.getCmp(this.config.id + '-element_name');
        if (value.value == 'attribute') {
            element_name.enable().show();
        }else{
            element_name.hide().disable();
        }
    },

    handleIndexFields: function (checkbox) {
        var index = Ext.getCmp(this.config.id + '-index');
        var subelement = Ext.getCmp(this.config.id + '-subelement');
        var subindex = Ext.getCmp(this.config.id + '-subindex');
        if (checkbox.checked) {
            index.enable().show();
            subelement.enable().show();
            subindex.enable().show();
        } else {
            index.hide().disable();
            subelement.hide().disable();
            subindex.hide().disable();
        }
    },
});
Ext.reg('shoplogistic-window-parser-config-fields-create', shopLogistic.window.CreateConfigField);


shopLogistic.window.UpdateConfigField = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_parser_config_fields_update'),
        width: 900,
        maxHeight: 400,
        baseParams: {
            action: 'mgr/parser/fields/update'
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateConfigField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateConfigField, shopLogistic.window.CreateConfigField, {

    getFields: function (config) {
        return shopLogistic.window.CreateConfigField.prototype.getFields.call(this, config)
    }

});
Ext.reg('shoplogistic-window-parser-config-fields-update', shopLogistic.window.UpdateConfigField);