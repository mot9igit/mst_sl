shopLogistic.panel.FileDocsPage = function (config) {
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
                title: _('shoplogistic_filedocs'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-files-docs',
                    id: 'shoplogistic-grid-files-docs',
                }]
            }, {
                title: _('shoplogistic_filedocs_statuses'),
                layout: 'anchor',
                items: [{
                    xtype: 'shoplogistic-grid-files-docs-statuses',
                    id: 'shoplogistic-grid-files-docs-statuses',
                }]
            }]
        }]
    });
    shopLogistic.panel.FileDocsPage.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic.panel.FileDocsPage, MODx.Panel);
Ext.reg('shoplogistic-panel-filedocs', shopLogistic.panel.FileDocsPage);