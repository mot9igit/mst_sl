shopLogistic.grid.ActionsStores = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-actions-stores-grid';
    }
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/actions/stores/getlist',
            action_id: config.record.object.id
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateActionStore(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'shoplogistic-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });

    shopLogistic.grid.ActionsStores.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.ActionsStores, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createActionStore: function (btn, e) {
        var w = MODx.load({
            xtype: 'shoplogistic-actions-stores-window-create',
            id: 'shoplogistic-actions-stores-window-create',
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({
            active: true,
            manual: true,
            action_id: this.config.record.object.id
        });
        //if(MODx.loadRTE) MODx.loadRTE("shoplogistic-actions-window-content");

        w.show(e.target);
    },

    updateActionStore: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/actions/stores/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-actions-stores-window-update',
                            id: 'shoplogistic-actions-stores-window-update',
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        //if(MODx.loadRTE) MODx.loadRTE("shoplogistic-actions-window-content");
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeActionStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('shoplogistic_action_stores_remove')
                : _('shoplogistic_action_store_remove'),
            text: ids.length > 1
                ? _('shoplogistic_action_stores_remove_confirm')
                : _('shoplogistic_action_store_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/actions/stores/remove',
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

    disableActionStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/actions/stores/disable',
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

    enableActionStore: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/actions/stores/enable',
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

    getFields: function () {
        return [
            'id',
            'store_id',
            'store_name',
            'action_id',
            'action_name',
            'description',
            'manual',
            'active',
            'createdon',
            'createdby',
            'updatedon',
            'updatedby',
            'properties',
            'actions'
        ];
    },

    getColumns: function (config) {
        var fields = [
            'id',
            'store_name',
            'description'
        ];
        var checks = [
            'manual',
            'active'
        ];
        var ff = [];
        fields.forEach(element => {
            ff.push({
                header: _('shoplogistic_action_store_' + element),
                dataIndex: element,
                sortable: true,
                width: 70
            });
        });
        checks.forEach(element => {
            ff.push({
                header: _('shoplogistic_action_store_' + element),
                dataIndex: element,
                renderer: shopLogistic.utils.renderBoolean,
                sortable: true,
                width: 100
            });
        });
        ff.push({
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        });
        return ff;
    },

    getTopBar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('shoplogistic_action_store_create'),
            handler: this.createActionStore,
            scope: this
        }, '->', {
            xtype: 'shoplogistic-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('shoplogistic-actions-stores-grid', shopLogistic.grid.ActionsStores);
