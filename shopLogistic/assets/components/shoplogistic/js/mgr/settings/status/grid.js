shopLogistic.grid.Status = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'shoplogistic-grid-status';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/settings/status/getlist',
            sort: 'rank',
            dir: 'asc',
        },
        stateful: true,
        stateId: config.id,
        ddGroup: 'sl-settings-status',
        ddAction: 'mgr/settings/status/sort',
        enableDragDrop: true,
        multi_select: true,
    });
    shopLogistic.grid.Status.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.grid.Status, shopLogistic.grid.Default, {

    getFields: function () {
        return [
            'id', 'name', 'description', 'color', 'stores_available', 'warehouses_available',
            'active', 'final', 'fixed', 'rank', 'ms2status_id', 'ms2status_name', 'ms2status_color', 'editable', 'actions'
        ];
    },

    getColumns: function () {
        return [
            {header: _('shoplogistic_id'), dataIndex: 'id', width: 30},
            {header: _('shoplogistic_name'), dataIndex: 'name', width: 50, renderer: shopLogistic.utils.renderBadge},
            {header: _('shoplogistic_ms2status_id'), dataIndex: 'ms2status_id', width: 50, renderer: shopLogistic.utils.renderBadgems2},
            {header: _('shoplogistic_status_stores_available'), dataIndex: 'stores_available', width: 50, renderer: this._renderBoolean},
            {header: _('shoplogistic_status_warehouses_available'), dataIndex: 'warehouses_available', width: 50, renderer: this._renderBoolean},
            {header: _('shoplogistic_status_final'), dataIndex: 'final', width: 50, renderer: this._renderBoolean},
            {header: _('shoplogistic_status_fixed'), dataIndex: 'fixed', width: 50, renderer: this._renderBoolean},
            {header: _('shoplogistic_rank'), dataIndex: 'rank', width: 35, hidden: true},
            {
                header: _('shoplogistic_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: shopLogistic.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i> ' + _('shoplogistic_btn_create'),
            handler: this.createStatus,
            scope: this
        }, '->', this.getSearchField()];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateStatus(grid, e, row);
            },
        };
    },

    createStatus: function (btn, e) {
        var w = Ext.getCmp('shoplogistic-window-status-create');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-status-create',
            id: 'shoplogistic-window-status-create',
            record: {
                color: '000000',
                active: 1
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.show(e.target);
    },

    updateStatus: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }

        var w = Ext.getCmp('shoplogistic-window-status-update');
        if (w) {
            w.close();
        }
        w = MODx.load({
            xtype: 'shoplogistic-window-status-update',
            id: 'shoplogistic-window-status-update',
            title: this.menu.record['name'],
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
        w.fp.getForm().setValues(this.menu.record);
        w.show(e.target);
    },

    enableStatus: function () {
        this.statusAction('enable');
    },

    disableStatus: function () {
        this.statusAction('disable');
    },

    removeStatus: function () {
        var ids = this._getSelectedIds();

        Ext.MessageBox.confirm(
            _('ms2_menu_remove_title'),
            ids.length > 1
                ? _('shoplogistic_menu_remove_multiple_confirm')
                : _('shoplogistic_menu_remove_confirm'),
            function (val) {
                if (val == 'yes') {
                    this.statusAction('remove');
                }
            },
            this
        );
    },

    _renderBoolean: function (value, cell, row) {
        var color, text;

        if (value == 0 || value == false || value == undefined) {
            color = 'red';
            text = _('no');
        } else {
            color = 'green';
            text = _('yes');
        }

        return row.data['active']
            ? String.format('<span class="{0}">{1}</span>', color, text)
            : text;
    },
});
Ext.reg('shoplogistic-grid-status', shopLogistic.grid.Status);