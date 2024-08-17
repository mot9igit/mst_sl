shopLogistic.window.UpdateDealField = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-field-deal-window-update';
    }
    Ext.applyIf(config, {
        title: _('shoplogistic_field_update'),
        width: 550,
        autoHeight: true,
        url: shopLogistic.config.connector_url,
        action: 'mgr/crm/fields/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    shopLogistic.window.UpdateDealField.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateDealField, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'type',
            id: config.id + '-type',
        }, {
            xtype: 'hidden',
            name: 'crm_id',
            id: config.id + '-crm_id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_crm_field_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_crm_field_enums'),
            name: 'enums',
            id: config.id + '-enums',
            anchor: '99%'
        }, {
            xtype: 'shoplogistic-combo-orderfield',
            fieldLabel: _('shoplogistic_crm_field_field'),
            name: 'field',
            id: config.id + '-field',
            anchor: '99%',
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_crm_field_properties'),
            name: 'properties',
            id: config.id + '-properties',
            anchor: '99%'
        }/*, {
            xtype: 'xcheckbox',
            boxLabel: _('dartcrm_field_active'),
            name: 'active',
            id: config.id + '-active',
        }*/];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-field-deal-window-update', shopLogistic.window.UpdateDealField);