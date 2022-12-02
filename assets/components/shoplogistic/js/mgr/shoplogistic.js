var shopLogistic = function (config) {
    config = config || {};
    shopLogistic.superclass.constructor.call(this, config);
};
Ext.extend(shopLogistic, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('shoplogistic', shopLogistic);

shopLogistic = new shopLogistic();