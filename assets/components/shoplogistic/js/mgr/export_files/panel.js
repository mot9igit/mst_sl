shopLogistic.panel.ExportFilesPage = function (config) {
    config = config || {};

    Ext.apply(config, {
        cls: 'container',
        items: [{
            xtype: 'modx-tabs',
            id: 'shoplogistic-filedocs-tabs',
            stateful: true,
            stateId: 'shoplogistic-filedocs-tabs',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            deferredRender: false,
            items: [{
                title: _('shoplogistic_export_files'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-export-files',
                    id: 'shoplogistic-grid-export-files',
                }]
            }, {
                title: _('shoplogistic_export_file_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-export-file-statuses',
                    id: 'shoplogistic-grid-export-file-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.ExportFilesPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.ExportFilesPage, MODx.Panel);
Ext.reg('shoplogistic-panel-export-files', shopLogistic.panel.ExportFilesPage);