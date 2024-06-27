/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

pimcore.registerNS("pimcore.plugin.CorepulseCacheBundle.settings");
/**
 * @private
 */
pimcore.plugin.CorepulseCacheBundle.settings = Class.create({

    initialize:function () {

        this.getTabPanel();
    },

    activate:function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem("panel_corepulsecache");
    },

    getTabPanel:function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id:"panel_corepulsecache",
                title:t("corepulse_cache"),
                iconCls:"pimcore_nav_icon_clear_cache",
                border:false,
                layout:"fit",
                closable:true,
                items:[this.getRowEditor()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("panel_corepulsecache");


            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("bundle_corepulsecache");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getRowEditor:function () {

        var url = Routing.generate('corepulsecache_settings');

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();

        this.store = pimcore.helpers.grid.buildDefaultStore(
            url,
            [
                {name:'id'},
                {name:'url'},               
                {name:'tags'},
                {name:'type'},
                {name:'active', type:'int'},
                {name:'updateAt'}
            ], itemsPerPage, {
                // remoteSort: false,
                remoteFilter: false
            }
        );
        this.store.setAutoSync(true);

        this.filterField = new Ext.form.TextField({
            width:200,
            style:"margin: 0 10px 0 0;",
            enableKeyEvents:true,
            listeners:{
                "keydown":function (field, key) {
                    if (key.getKey() == key.ENTER) {
                        var input = field;
                        var proxy = this.store.getProxy();
                        proxy.extraParams.filter = input.getValue();
                        this.store.load();
                    }
                }.bind(this)
            }
        });

        this.pagingToolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.store);

        var typesColumns = [
            {text:t("url"), flex:100, sortable:true, dataIndex:'url', editable: false},
            {text:t("tags"), flex:50, sortable:false, dataIndex:'tags', editable: false},
            {text:t("type"), flex:50, sortable:false, dataIndex:'type', editable: false},
            {text:t("active"), flex:50, sortable:false, dataIndex:'active',
                editor:new Ext.form.ComboBox({
                    store:[[1, 'On'], [0, 'Off']],
                    mode:"local",
                    // triggerAction:"all"
                }),
                renderer: function(d) {
                    if (d) {
                        return '<span style="background-color: #28a745;padding-left: 10px;padding-right: 10px;border-radius: 5px;color: white;">On</span>';
                    } else {
                        return '<span style="background-color: #dc3545;padding-left: 10px;padding-right: 10px;border-radius: 5px;color: white;">Off</span>';
                    }
                }
            },
            {text: t("status"), flex:50, sortable: false, dataIndex: 'status', editable: false,
                // hidden: true,
                renderer: function(d) {
                    if (d) {
                        return '<span style="background-color: #28a745;padding-left: 10px;padding-right: 10px;border-radius: 5px;color: white;">Success</span>';
                    } else {
                        return '<span style="background-color: #dc3545;padding-left: 10px;padding-right: 10px;border-radius: 5px;color: white;">Failed</span>';
                    }
                }
            },
            {text: t("modificationDate"), flex:50, sortable: true, dataIndex: 'updateAt', editable: false,
                // hidden: true,
                renderer: function(d) {
                    if (d !== undefined) {
                        var date = new Date(d);
                        return Ext.Date.format(date, "Y-m-d H:i:s");
                    } else {
                        return "";
                    }
                }
            },
            {
                xtype:'actioncolumn',
                menuText: t('Action'),
                width: 80,
                items: [{
                    getClass: function (v, meta, rec) {
                        var klass = "pimcore_action_column ";
                        if (rec.data.writeable) {
                            klass += "pimcore_icon_clear_cache";
                        }
                        return klass;
                    },
                    tooltip: t('Clear'),
                    handler: function (grid, rowIndex) {
                        var data = grid.getStore().getAt(rowIndex);
                        if (!data.data.writeable) {
                            return;
                        }
                        window.khanh = grid;
                        const decodedName = Ext.util.Format.htmlDecode(data.data.url);

                        Ext.Msg.confirm(t('clear_cache'), 'Do you really want to delete cache: '+ decodedName,
                        function (btn) {
                            if (btn == 'yes') {
                                Ext.Ajax.request({
                                    url: Routing.generate('corepulsecache_clear_all'),
                                    method: 'POST',
                                    params: {
                                        id: data.data.id
                                    },
                                    success: function (response) {
                                        grid.getStore().reload();
                                        grid.focusRow(rowIndex);
                                    }.bind(this)
                                });
                            }

                            return;
                        }.bind(this))
                       
                    }.bind(this)
                },{
                    getClass: function (v, meta, rec) {
                        var klass = "pimcore_action_column ";
                        if (rec.data.writeable) {
                            klass += "pimcore_icon_minus";
                        }
                        return klass;
                    },
                    tooltip: t('delete'),
                    handler: function (grid, rowIndex) {
                        var data = grid.getStore().getAt(rowIndex);
                        if (!data.data.writeable) {
                            return;
                        }

                        const decodedName = Ext.util.Format.htmlDecode(data.data.url);

                        pimcore.helpers.deleteConfirm(
                            t('corepulse_cache'),
                            Ext.util.Format.htmlEncode(decodedName),
                            function () {
                                grid.getStore().removeAt(rowIndex);
                            }.bind(this)
                        );
                    }.bind(this)
                }]
            }
        ];

        this.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            clicksToMoveEditor: 1,
            listeners: {
                beforeedit: function (editor, context, eOpts) {
                    if (!context.record.data.writeable) {
                        return false;
                    }
                }
            }
        });


        this.grid = Ext.create('Ext.grid.Panel', {
            frame:false,
            autoScroll:true,
            store:this.store,
            columnLines:true,
            bodyCls: "pimcore_editable_grid",
            trackMouseOver:true,
            stripeRows:true,
            columns: {
                items: typesColumns,
                defaults: {
                    renderer: Ext.util.Format.htmlEncode
                },
            },
            sm: Ext.create('Ext.selection.RowModel', {}),
            plugins: [
                this.rowEditing
            ],
            tbar: {
                cls: 'pimcore_main_toolbar',
                items: [
                    {
                        text:t("filter") + "/" + t("search"),
                        xtype:"tbtext",
                        style:"margin: 0 10px 0 0;"
                    },
                    this.filterField,
                    "->",
                    {
                        text:t('clear_all'),
                        handler:this.clearAll.bind(this),
                        iconCls:"pimcore_icon_delete",
                        // disabled: !pimcore.settings['staticroutes-writeable']
                    }
                ]
            },
            // paging bar on the bottom
            bbar: this.pagingToolbar,
            viewConfig:{
                forceFit:true,
                getRowClass: function (record, rowIndex) {
                    return record.data.writeable ? '' : 'pimcore_grid_row_disabled';
                }
            }
        });

        return this.grid;
    },
    clearAll: function (btn, ev) {
        Ext.Ajax.request({
            url: Routing.generate('corepulsecache_clear_all'),
            method: 'POST',
            params: {},
            success: function (response) {
                this.store.load();
            }.bind(this)
        });
    },

    onAdd:function (btn, ev) {
        var u = {
            url: ""
        };

        this.grid.store.add(u);
    }
});
