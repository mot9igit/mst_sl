shopLogistic.panel.RemainsForm = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic_remains-form';
    }

    Ext.apply(config, {
        layout: 'form',
        cls: 'main-wrapper',
        defaults: {msgTarget: 'under', border: false},
        anchor: '100% 100%',
        border: false,
        items: this.getFields(config),
        listeners: this.getListeners(config),
        buttons: this.getButtons(config),
        keys: this.getKeys(config),
    });
    shopLogistic.panel.RemainsForm.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.RemainsForm, MODx.FormPanel, {

    grid: null,

    getFields: function (config) {
        return [{
            layout: 'column',
            items: [{
                columnWidth: .308,
                layout: 'form',
                defaults: {anchor: '100%', hideLabel: true},
                items: this.getLeftFields(config),
            }, {
                columnWidth: .37,
                layout: 'form',
                defaults: {anchor: '100%', hideLabel: true},
                items: this.getCenterFields(config),
            }, {
                columnWidth: .322,
                layout: 'form',
                defaults: {anchor: '100%', hideLabel: true},
                items: this.getRightFields(config),
            }],
        }];
    },

    getLeftFields: function (config) {
        return [{
            xtype: 'textfield',
            id: config.id + '-search',
            emptyText: _('shoplogistic_form_search'),
            name: 'query',
        },{
            xtype: 'shoplogistic-combo-published',
            id: config.id + '-published',
            emptyText: _('shoplogistic_store_remain_published'),
            name: 'published',
            addall: true,
            listeners: {
                select: {
                    fn: function () {
                        this.fireEvent('change')
                    }, scope: this
                }
            }
        }];
    },

    getCenterFields: function (config) {
        return [{
            xtype: 'shoplogistic-combo-remain-status',
            id: config.id + '-status',
            emptyText: _('shoplogistic_store_remain_status'),
            name: 'status',
            addall: true,
            listeners: {
                select: {
                    fn: function () {
                        this.fireEvent('change')
                    }, scope: this
                }
            }
        },{
            xtype: 'shoplogistic-combo-copo',
            id: config.id + '-copo',
            emptyText: _('shoplogistic_store_remain_copo'),
            name: 'copo',
            addall: true,
            listeners: {
                select: {
                    fn: function () {
                        this.fireEvent('change')
                    }, scope: this
                }
            }
        }];
    },

    getRightFields: function (config) {
        return [{
            xtype: 'shoplogistic-combo-store',
            id: config.id + '-store_id',
            emptyText: _('shoplogistic_store_remain_store_id'),
            name: 'store_id',
            allowBlank: true,
            listeners: {
                select: {
                    fn: function () {
                        this.fireEvent('change')
                    }, scope: this
                }
            }
        }];
    },

    getListeners: function () {
        return {
            beforerender: function () {
                this.grid = Ext.getCmp('shoplogistic-grid-storeremains');
                var store = this.grid.getStore();
                var form = this;
                store.on('load', function (res) {
                    // form.updateInfo(res.reader['jsonData']);
                });
            },
            afterrender: function () {
                var form = this;
                window.setTimeout(function () {
                    form.on('resize', function () {
                        // form.updateInfo();
                    });
                }, 100);
            },
            change: function () {
                this.submit();
            },
        }
    },

    getButtons: function () {
        return [{
            text: '<i class="icon icon-times"></i> ' + _('shoplogistic_form_reset'),
            handler: this.reset,
            scope: this,
            iconCls: 'x-btn-small',
        }, {
            text: '<i class="icon icon-check"></i> ' + _('shoplogistic_form_submit'),
            handler: this.submit,
            scope: this,
            cls: 'primary-button',
            iconCls: 'x-btn-small',
        }];
    },

    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            fn: function () {
                this.submit();
            },
            scope: this
        }];
    },

    submit: function () {
        var store = this.grid.getStore();
        var form = this.getForm();

        var values = form.getFieldValues();
        for (var i in values) {
            if (i != undefined && values.hasOwnProperty(i)) {
                store.baseParams[i] = values[i];
            }
        }
        this.refresh();
    },

    reset: function () {
        var store = this.grid.getStore();
        var form = this.getForm();

        form.items.each(function (f) {
            if (f.name == 'status') {
                f.clearValue();
            } else {
                f.reset();
            }
        });

        var values = form.getValues();
        for (var i in values) {
            if (values.hasOwnProperty(i)) {
                store.baseParams[i] = '';
            }
        }
        this.refresh();
    },

    refresh: function () {
        this.grid.getBottomToolbar().changePage(1);
    },

    focusFirstField: function () {
    },

});
Ext.reg('shoplogistic_remains-form', shopLogistic.panel.RemainsForm);
