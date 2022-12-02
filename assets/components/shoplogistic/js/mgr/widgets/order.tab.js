Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function () {
    this.fields.items.push({
        xtype: 'shoplogistic-order-delivery-panel',
        title: _('shoplogistic_order_delivery_tab'),
        order_id: this.record.id || 0,
    });
});