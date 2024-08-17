shopLogistic.grid.Page = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-page';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/adv/pages/getlist',
            sort: 'id',
            dir: 'desc',
            // store_id: config.record.object.id
        },
        stateful: true,
        // stateId: config.record.object.id,
    });
    shopLogistic.grid.Page.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Page, shopLogistic.grid.Default, {

    getFields: function () {
        return ['id', 'resource_id', 'name', 'active', 'description', 'actions'];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_page_create'),
            handler: this.createPage,
            scope: this
        }];
    },

    getColumns: function () {
        return [
            {
                header: _('shoplogistic_page_id'),
                dataIndex: 'id',
                width: 20
            },
            {
                header: _('shoplogistic_page_resource_id'),
                width: 20,
                dataIndex: 'resource_id'
            },
            {
                header: _('shoplogistic_page_name'),
                dataIndex: 'name',
                sortable: true,
                width: 100,
            },
            {
                header: _('shoplogistic_page_active'),
                dataIndex: 'active',
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 50,
            },
            {
                header: _('shoplogistic_page_description'),
                dataIndex: 'description',
                width: 100
            },
            {
                header: _('shoplogistic_grid_actions'),
                dataIndex: 'actions',
                renderer: shopLogistic.utils.renderActions,
                sortable: false,
                width: 150,
                id: 'actions'
            }
        ];
    },


    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updatePage(grid, e, row);
            },
        };
    },

    createPage: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-page-create');
        if (w) {
            w.hide().getEl().remove();
        }

        w = MODx.load({
            xtype: 'shoplogistic-window-page-create',
            id: 'shoplogistic-window-page-create',
            record: this.menu.record,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues({});
        w.show(e.target);
    },

    removePage: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_actions_remove')
                : _('shoplogistic_action_remove'),
            text: ids.length > 1
                ? _('shoplogistic_actions_remove_confirm')
                : _('shoplogistic_action_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/adv/pages/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },

    disablePage: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/pages/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enablePage: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/adv/pages/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    updatePage: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-page-updater');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-page-update',
            id: 'shoplogistic-window-page-updater',
            record: this.menu.record,
            title: this.menu.record['store'],
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.fp.getForm().reset();
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    }
});
Ext.reg('shoplogistic-grid-page', shopLogistic.grid.Page);