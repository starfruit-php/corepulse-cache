pimcore.registerNS("pimcore.plugin.CorepulseCacheBundle");

pimcore.plugin.CorepulseCacheBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        let menu = e.detail.menu;
        // const user = pimcore.globalmanager.get('user');
        // const perspectiveCfg = pimcore.globalmanager.get("perspective");

        // if (user.isAllowed("routes") && perspectiveCfg.inToolbar("settings.routes")) {
        menu.settings.items.push({
            text: t("corepulse_cache"),
            iconCls: "pimcore_nav_icon_clear_cache",
            priority: 96,
            itemId: 'corepulse_cache',
            handler: this.editCache
        });
        // }
    },

    editCache: function () {

        try {
            pimcore.globalmanager.get("bundle_corepulsecache").activate();
        }
        catch (e) {
            pimcore.globalmanager.add("bundle_corepulsecache", new pimcore.plugin.CorepulseCacheBundle.settings());
        }
    },

    pimcoreReady: function (e) {
        // alert("CorepulseCacheBundle ready!");
    }
});

var CorepulseCacheBundlePlugin = new pimcore.plugin.CorepulseCacheBundle();
