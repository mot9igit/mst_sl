shopLogistic.window.CreateOrgRequisites = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgrequisites_create'),
        width: 600,
        baseParams: {
            action: 'mgr/org/requisites/create',
        },
    });
    shopLogistic.window.CreateOrgRequisites.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.CreateOrgRequisites, shopLogistic.window.Default, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'org_id',
            id: config.id + '-org_id',
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_ogrn'),
            name: 'ogrn',
            id: config.id + '-ogrn',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_inn'),
            name: 'inn',
            id: config.id + '-inn',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_kpp'),
            name: 'kpp',
            id: config.id + '-kpp',
            anchor: '99%'
        },
        {
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_ur_address'),
            name: 'ur_address',
            id: config.id + '-ur_address',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_fact_address'),
            name: 'fact_address',
            id: config.id + '-fact_address',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_orgrequisites_ur_address'),
            name: 'ur_address',
            id: config.id + '-ur_address',
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_orgrequisites_marketplace'),
            name: 'marketplace',
            id: config.id + '-marketplace',
            checked: true,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_orgrequisites_send_request'),
            name: 'send_request',
            id: config.id + '-send_request',
            checked: true,
        }];
    },
});
Ext.reg('shoplogistic-window-orgrequisites-create', shopLogistic.window.CreateOrgRequisites);


shopLogistic.window.UpdateOrgRequisites = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('shoplogistic_orgrequisites_update'),
        baseParams: {
            action: 'mgr/org/requisites/update',
        },
        bodyCssClass: 'tabs',
    });
    shopLogistic.window.UpdateOrgRequisites.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateOrgRequisites, shopLogistic.window.CreateOrgRequisites, {

    // getFields: function (config) {
    //     return shopLogistic.window.CreateOrg.prototype.getFields.call(this, config);
    // }

    getFields: function (config) {

        var default_tabs = [{
            title: _('shoplogistic_org_requisites'),
            layout: 'form',
            items: shopLogistic.window.CreateOrgRequisites.prototype.getFields.call(this, config)
        }, {
            title: _('shoplogistic_orgrequisitesbank'),
            items: [{
                xtype: 'shoplogistic-grid-orgrequisitesbank',
                record: config.record,
            }]
        }];

        return [{
            xtype: 'modx-tabs',
            items: default_tabs
        }];
    }

});
Ext.reg('shoplogistic-window-orgrequisites-update', shopLogistic.window.UpdateOrgRequisites);