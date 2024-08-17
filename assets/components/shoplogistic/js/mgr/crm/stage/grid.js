shopLogistic.grid.Stage = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-crm-grid-stage';
    }
    Ext.applyIf(config, {
        url: shopLogistic.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/crm/stage/getlist',
            category_id: config.record.object.id
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStage(grid, e, row);
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
    shopLogistic.grid.Stage.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(shopLogistic.grid.Stage, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = shopLogistic.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    updateStage: function (btn, e, row) {
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
                action: 'mgr/crm/stage/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'shoplogistic-stage-window-update',
                            id: Ext.id(),
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
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    getFields: function () {
        return [
            'id',
            'name',
            'check_code',
            'user_description',
            'transition_to',
            'transition_fail',
            'transition_anchor',
            'category_id',
            'active',
            'to_tk',
            'pay',
            'payment_bonus',
            'check_deal',
            'check_code',
            'crm_id',
            'sort',
            'properties',
            'stores_available',
            'warehouses_available',
            'actions'
        ];
    },

    getColumns: function () {
        return [{
            header: _('shoplogistic_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('shoplogistic_crm_deal_stage_name'),
            dataIndex: 'name',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_crm_deal_stage_sort'),
            dataIndex: 'sort',
            sortable: true,
            width: 200,
        },{
            header: _('shoplogistic_crm_deal_stage_transition_to'),
            dataIndex: 'transition_to',
            sortable: true,
            width: 200
        },{
            header: _('shoplogistic_crm_deal_stage_transition_anchor'),
            dataIndex: 'transition_anchor',
            sortable: true,
            width: 200
        }, {
            header: _('shoplogistic_crm_deal_stage_crm_id'),
            dataIndex: 'crm_id',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_crm_deal_stage_check_deal'),
            dataIndex: 'check_deal',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_check_code'),
            dataIndex: 'check_code',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_to_tk'),
            dataIndex: 'to_tk',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_pay'),
            dataIndex: 'pay',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_payment_bonus'),
            dataIndex: 'payment_bonus',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_stores_available'),
            dataIndex: 'stores_available',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        },{
            header: _('shoplogistic_crm_deal_stage_warehouses_available'),
            dataIndex: 'warehouses_available',
            renderer: shopLogistic.utils.renderBoolean,
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_crm_deal_stage_properties'),
            dataIndex: 'properties',
            sortable: false,
            width: 250,
        }, {
            header: _('shoplogistic_grid_actions'),
            dataIndex: 'actions',
            renderer: shopLogistic.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
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
Ext.reg('shoplogistic-crm-grid-stage', shopLogistic.grid.Stage);
