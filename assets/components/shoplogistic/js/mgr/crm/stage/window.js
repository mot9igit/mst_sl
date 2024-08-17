shopLogistic.window.UpdateStage = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-stage-window-update';
    }
    Ext.applyIf(config, {
        title: _('shoplogistic_crm_deal_stage_update'),
        width: 550,
        autoHeight: true,
        url: shopLogistic.config.connector_url,
        action: 'mgr/crm/stage/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    shopLogistic.window.UpdateStage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateStage, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'category_id',
            id: config.id + '-category_id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_crm_deal_stage_crm_id'),
            name: 'crm_id',
            id: config.id + '-crm_id',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_crm_deal_stage_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%'
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('shoplogistic_crm_deal_stage_sort'),
            name: 'sort',
            id: config.id + '-sort',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-stage',
            fieldLabel: _('shoplogistic_crm_deal_stage_transition_to'),
            name: 'transition_to',
            id: config.id + '-transition_to',
            anchor: '99%'
        },{
            xtype: 'shoplogistic-combo-stage',
            fieldLabel: _('shoplogistic_crm_deal_stage_transition_fail'),
            name: 'transition_fail',
            id: config.id + '-transition_fail',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_crm_deal_stage_transition_anchor'),
            name: 'transition_anchor',
            id: config.id + '-transition_anchor',
            anchor: '99%'
        },{
            xtype: 'textfield',
            fieldLabel: _('shoplogistic_crm_deal_stage_user_description'),
            name: 'user_description',
            id: config.id + '-user_description',
            anchor: '99%'
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_to_tk'),
            name: 'to_tk',
            id: config.id + '-to_tk',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_check_deal'),
            name: 'check_deal',
            id: config.id + '-check_deal',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_pay'),
            name: 'pay',
            id: config.id + '-pay',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_payment_bonus'),
            name: 'payment_bonus',
            id: config.id + '-payment_bonus',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_check_code'),
            name: 'check_code',
            id: config.id + '-check_code',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_stores_available'),
            name: 'stores_available',
            id: config.id + '-stores_available',
            checked: false,
        },{
            xtype: 'xcheckbox',
            boxLabel: _('shoplogistic_crm_deal_stage_warehouses_available'),
            name: 'warehouses_available',
            id: config.id + '-warehouses_available',
            checked: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_crm_deal_stage_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%'
        } ,{
            xtype: 'textarea',
            fieldLabel: _('shoplogistic_crm_deal_stage_properties'),
            name: 'properties',
            id: config.id + '-properties',
            anchor: '99%'
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-stage-window-update', shopLogistic.window.UpdateStage);