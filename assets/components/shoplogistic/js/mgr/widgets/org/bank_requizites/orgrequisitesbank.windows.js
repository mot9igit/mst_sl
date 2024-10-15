shopLogistic.window.CreateOrgRequisitesBank = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgrequisitesbank_create'),
        width: 600,
        baseParams: {
            action: 'mgr/org/requisites/bank/create',
        },
    });
    shopLogistic.window.CreateOrgRequisitesBank.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrgRequisitesBank, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'org_requisite_id',
            id: config.id + '-org_requisite_id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisitesbank_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisitesbank_bank_name'),
            name: 'bank_name',
            id: config.id + '-bank_name',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisitesbank_bank_number'),
            name: 'bank_number',
            id: config.id + '-bank_number',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisitesbank_bank_knumber'),
            name: 'bank_knumber',
            id: config.id + '-bank_knumber',
            anchor: '99%'
        },
        {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisitesbank_bank_bik'),
            name: 'bank_bik',
            id: config.id + '-bank_bik',
            anchor: '99%'
        }];
    },
});
Ext.reg('shoplogistic-window-orgrequisitesbank-create', shopLogistic.window.CreateOrgRequisitesBank);


shopLogistic.window.UpdateOrgRequisitesBank = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgrequisitesbank_update'),
        baseParams: {
            action: 'mgr/org/requisites/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrgRequisitesBank.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrgRequisitesBank, shopLogistic.window.CreateOrgRequisitesBank, {

    getFields: function (config) {
        return shopLogistic.window.CreateOrgRequisitesBank.prototype.getFields.call(this, config);
    }

});
Ext.reg('shoplogistic-window-orgrequisitesbank-update', shopLogistic.window.UpdateOrgRequisitesBank);