shopLogistic.window.UpdateCategory = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-category-window-update';
    }
    Ext.applyIf(config, {
        title: _('shoplogistic_crm_deal_category_update'),
        width: 550,
        autoHeight: true,
        url: shopLogistic.config.connector_url,
        action: 'mgr/crm/category/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    shopLogistic.window.UpdateCategory.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.window.UpdateCategory, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'modx-tabs',
            items: [{
                title: _('shoplogistic_crm_deal_category_update'),
                layout: 'form',
                items: [{
                    xtype: 'hidden',
                    name: 'id',
                    id: config.id + '-id',
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_crm_deal_category_crm_id'),
                    name: 'crm_id',
                    id: config.id + '-crm_id',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_crm_deal_category_name'),
                    name: 'name',
                    id: config.id + '-name',
                    anchor: '99%'
                }, {
                    xtype: 'statictextfield',
                    fieldLabel: _('shoplogistic_crm_deal_category_sort'),
                    name: 'sort',
                    id: config.id + '-sort',
                    anchor: '99%'
                }, {
                    xtype: 'textarea',
                    fieldLabel: _('shoplogistic_crm_deal_category_properties'),
                    name: 'properties',
                    id: config.id + '-properties',
                    anchor: '99%'
                }]
            }, {
                title: _('shoplogistic_crm_deal_stage'),
                items: [{
                    xtype: 'shoplogistic-crm-grid-stage',
                    record: config.record,
                }]
            }]
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('shoplogistic-category-window-update', shopLogistic.window.UpdateCategory);