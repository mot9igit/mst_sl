shopLogistic.grid.Actions = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-actions-grid';
    }
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/actions/items/getlist',
            type: config.type
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateAction(grid, e, row);
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

    shopLogistic.grid.Actions.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.Actions, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createAction: function (btn, e) {
        var w = MODx.load({
            xtype: 'shoplogistic-actions-window-create',
            id: Ext.id(),
            type: this.config.baseParams.type,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});

        //if(MODx.loadRTE) MODx.loadRTE("shoplogistic-actions-window-content");

        w.show(e.target);
    },

    updateAction: function (btn, e, row) {
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
                action: 'mgr/actions/items/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-actions-window-update',
                            id: Ext.id(),
                            record: r,
                            type: r.object.type,
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
                        if(r.object["regions"]){
                            Ext.getCmp('shoplogistic-actions-regions').setValueEx( r.object["regions"] );
                        }
                        if(r.object["cities"]){
                            Ext.getCmp('shoplogistic-actions-cities').setValueEx( r.object["cities"] );
                        }
                        //if(MODx.loadRTE) MODx.loadRTE("shoplogistic-actions-window-content");
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeAction: function () {
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
                action: 'mgr/actions/items/remove',
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

    disableAction: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/actions/items/disable',
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

    enableAction: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/actions/items/enable',
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
            'name',
            'image',
            'image_inner',
            'description',
            'resource',
            'resource_id',
            'regions',
            'regions_name',
            'cities',
            'cities_name',
            'content',
            'date_from',
            'date_to',
            'global',
            'store_id',
            'store_name',
            'active' ,
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
            'name',
            'regions_name',
            'cities_name',
            'store_name',
            'description'
        ];
        var checks = [
            'global',
            'active'
        ];
        var ff = [];
        fields.forEach(element => {
            ff.push({
                header: _('shoplogistic_action_' + element),
                dataIndex: element,
                sortable: true,
                width: 70
            });
        });
        checks.forEach(element => {
            ff.push({
                header: _('shoplogistic_action_' + element),
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
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('shoplogistic_action_create'),
            handler: this.createAction,
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
Ext.reg('shoplogistic-actions-grid', shopLogistic.grid.Actions);
