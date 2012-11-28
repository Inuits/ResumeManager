/**
 * Select language.
 */
var resumeExportSelectLang = function(uid) {
    var langData = [ {
        value: 'en',
        name: 'English'
    } ];

    for ( var langKey in langs) {
        langData.push( {
            value: langKey,
            name: langs[langKey]
        });
    }

    var lang = Ext.create('Ext.data.Store', {
        fields: [ 'name', 'value' ],
        data: langData
    });
    var buildTitle = Ext.Date.format(new Date(), 'Y-m-d G:i');

    var win;
    var form = Ext.widget('form', {
        layout: 'anchor',
        border: false,
        bodyPadding: '20px 10px 20px 20px',
        defaultType: 'textfield',
        frame: true,
        fieldDefaults: {
            labelAlign: 'left',
            labelWidth: 80,
            labelStyle: 'font-weight:normal'
        },
        items: [ {
            fieldLabel: 'Build name',
            name: 'name',
            emptyText: buildTitle,
            anchor: '100%',
            allowBlank: true
        }, {
            xtype: 'combo',
            mode: 'local',
            value: 'en',
            triggerAction: 'all',
            forceSelection: true,
            editable: false,
            allowBlank: false,
            fieldLabel: 'Language',
            name: 'language',
            displayField: 'name',
            valueField: 'value',
            queryMode: 'local',
            anchor: '100%',
            store: lang
        }, {
            xtype: 'hidden',
            value: uid,
            name: 'uid'
        }, {
            xtype: 'hidden',
            value: buildTitle,
            name: 'emptyname'
        }, {
            xtype: 'hidden',
            value: Ext.Date.format(new Date(), 'Y-m-d'),
            name: 'date'
        } ],

        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('window').hide();
            }
        }, {
            text: 'Create',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/build/create',
                        waitTitle: 'Please wait',
                        waitMsg: 'Building in process...',
                        success: function(form, action) {
                            resumeExport(action.result.bid);
                        },
                        failure: function(form, action) {
                            alert('Error');
                        }
                    });
                    this.up('window').hide();
                }
            }
        } ]
    });

    win = Ext.widget('window', {
        title: 'Build new resume',
        iconCls: 'wand-small',
        width: 350,
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: form
    }).show();
}

/**
 * Export forms (tab list).
 */
var winExistingBuilds;
var resumeExistingBuildsStore;
var careerStore = false;
var resumeExport = function(id, beforedestroyFn) {
    if (!beforedestroyFn) {
        beforedestroyFn = function() {
        };
    }
    if (!careerStore) {
        careerStore = Ext.create('Ext.data.Store', {
            fields: [ {
                type: 'string',
                name: 'name'
            } ],
            data: []
        });
    }

    var tabItems = new Array();
    var tabData = new Array();
    var resumeSelectebleTypes = {
        career: 'Career history',
        training: 'Training',
        education: 'Education',
        publication: 'Publication',
        talk: 'Talk',
        certification: 'Certification',
        project: 'Project'
    };

    /**
     * Profile
     */
    var profileForm = Ext.widget('form', {
        layout: 'anchor',
        border: false,
        bodyPadding: 5,
        defaultType: 'textfield',
        frame: true,
        fieldDefaults: {
            labelAlign: 'top'
        },
        items: [ {
            name: 'profile',
            fieldLabel: 'Profile',
            xtype: 'textareafield',
            anchor: '100%',
            height: 180
        }, {
            weight: 1,
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'footer',
            items: [ '->', {
                xtype: 'splitbutton',
                text: 'Cancel',
                menu: [ {
                    text: 'Reload data from user profile',
                    listeners: {
                        click: function() {
                            profileForm.getForm().load( {
                                url: '/build/profile/reload/1/id/' + id,
                                waitMsg: 'Loading...',
                                failure: function(form, action) {
                                    Ext.Msg.alert("Load failed", action.result.errorMessage);
                                }
                            });
                        }
                    }
                } ],
                listeners: {
                    click: function() {
                        profileForm.getForm().load( {
                            url: '/build/profile/id/' + id,
                            waitMsg: 'Loading...',
                            failure: function(form, action) {
                                Ext.Msg.alert("Load failed", action.result.errorMessage);
                            }
                        });
                    }
                }
            }, {
                xtype: 'button',
                text: 'Save profile',
                listeners: {
                    click: function() {
                        this.up('form').getForm().submit( {
                            clientValidation: true,
                            url: '/build/profile/id/' + id,
                            waitTitle: 'Please wait',
                            waitMsg: 'Saving...',
                            failure: function(form, action) {
                                Ext.Msg.alert("Error", action.result.errorMessage);
                            }
                        });
                    }
                }
            } ]
        } ]
    });

    profileForm.getForm().load( {
        url: '/build/profile/id/' + id,
        waitMsg: 'Loading...',
        failure: function(form, action) {
            Mask.hide();
            Ext.Msg.alert("Load failed", action.result.errorMessage);
        }
    });

    tabItems[0] = {
        title: 'Profile',
        iconCls: 'user-small',
        items: profileForm
    };

    /**
     * Blocks.
     */
    var blocksUpd = function() {
        for ( var key in resumeSelectebleTypes) {
            var checkbox = Ext.getCmp(key);
            var tab = Ext.getCmp('tab' + key);
            if (checkbox.getValue()) {
                tab.tab.setDisabled(false);
            } else {
                tab.tab.setDisabled(true);
            }
        }
        blocksForm.getForm().submit( {
            clientValidation: true,
            waitTitle: 'Please wait',
            waitMsg: 'Saving...',
            url: '/build/blocks/id/' + id,
            failure: function(form, action) {
                Ext.Msg.alert("Error", action.result.errorMessage);
            }
        });
    };

    var checkboxItems = Array();
    for ( var key in resumeSelectebleTypes) {
        exportGrid[key + 'Store'] = new Ext.data.Store(resumeExportDataStore(key, id));
        exportGrid[key] = Ext.create('Ext.grid.Panel', resumeExportGridPanel(key, resumeSelectebleTypes[key]));

        // Checkboxes.
        checkboxItems.push( {
            checked: true,
            boxLabel: resumeSelectebleTypes[key],
            id: key,
            name: key
        });

        // Tabs.
        tabItems.push( {
            title: resumeSelectebleTypes[key],
            id: 'tab' + key,
            frame: true,
            bodyPadding: 2,
            iconCls: 'grid-small-dot',
            items: exportGrid[key]
        });
    }

    var blocksForm = Ext.widget('form', {
        layout: 'anchor',
        border: false,
        frame: true,
        bodyPadding: '15px 15px 10px 15px',
        defaultType: 'checkbox',
        items: [{
            xtype: 'fieldset',
            flex: 1,
            title: 'Step 1: Select types to export',
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'checkboxgroup',
                defaults: {
                    listeners: {
                        click: {
                            element: 'el',
                            fn: blocksUpd
                        }
                    }
                },
                title: false,
                columns: 1,
                items: checkboxItems
            }]
        }, {
            xtype: 'fieldset',
            margin: '15px 0px 0px 0px',
            flex: 1,
            title: 'Step 2: Download CV',
            layout: 'anchor',
            items: [ {
                text: 'Download PDF',
                xtype: 'button',
                scale: 'medium',
                iconCls: 'document-pdf',
                margin: 5,
                listeners: {
                    click: function() {
                        window.open('/build/export/type/pdf/id/' + id);
                    }
                }
            }, {
                text: 'Download RTF',
                xtype: 'button',
                scale: 'medium',
                iconCls: 'document-word',
                margin: 5,
                listeners: {
                    click: function() {
                        window.open('/build/export/type/rtf/id/' + id);
                    }
                }
            } ]
        }]
    });

    blocksForm.getForm().load( {
        url: '/build/blocks/id/' + id,
        success: function(form, action) {
            blocksUpd();
        },
        failure: function(form, action) {
            Ext.Msg.alert("Load failed", action.result.errorMessage);
        }
    });

    //    tabItems[1] = {
    //        title: 'Export CV',
    //        iconCls: 'gear-small',
    //        items: blocksForm
    //    };

    // Window
    var subTabs = Ext.create('Ext.tab.Panel', {
        resizeTabs: true,
        enableTabScroll: true,
        defaults: {
            closable: false
        },
        items: tabItems
    });
    
    var tabs = Ext.create('Ext.tab.Panel', {
        resizeTabs: true,
        enableTabScroll: true,
        defaults: {
            closable: false
        },
        items: [{
            title: 'Export CV',
            iconCls: 'disk-small-black',
            items: blocksForm
        }, {
            title: 'Preview or edit resume build',
            iconCls: 'gear-small',
            items: subTabs
        }]
    });
    var win = Ext.createWidget('window', {
        width: 950,
        iconCls: 'wand-small',
        title: 'Resume build #' + id,
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        listeners: {
            afterrender: function() {
                Mask.hide();
            },
            beforedestroy: function() {
                beforedestroyFn();
            }
        },
        items: tabs
    });
    win.show();

};

var resumeExportDataStore = function(key, resumeId) {
    var fields = cvData['globalProfile'][key]['fields'];
    if (key == 'career') {
        fields.push( {
            name: 'omit_date',
            type: 'bool'
        });
        fields.push( {
            name: 'client_info',
            type: 'bool'
        });
        fields.push( {
            name: 'category',
            type: 'string'
        });
    }

    var store = {
        autoLoad: true,
        // autoSync: true,
        fields: fields,
        proxy: {
            type: 'ajax',
            url: '/build/grid/type/' + key + '/id/' + resumeId,
            reader: {
                type: 'json',
                root: 'data'
            },
            writer: {
                type: 'json'
            }
        }
    };

    if (key == 'career') {
        store.groupField = 'category';
    }
    return store;
}

var exportGrid = new Object();
var resumeExportGridPanel = function(key, title) {
    var gridPanel = new Object();

    var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
        clicksToEdit: 1
    });

    var columns = cvData['globalProfile'][key]['columns'];

    // Check column.
    var checkcolumn = false;
    for ( var key2 in columns) {
        if (columns[key2]['xtype'] == 'checkcolumn') {
            checkcolumn = true;
        }
    }
    if (key == 'publication' || key == 'talk') {
        columns[3].renderer = false;
    }
    if (!checkcolumn && key == 'career') {
        var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1,
            listeners: {
                edit: function() {
                    var view = exportGrid.career.getView();
                    view.refresh();
                }
            }
        });
        columns.push( {
            xtype: 'checkcolumn',
            header: 'Omit date',
            dataIndex: 'omit_date',
            width: 60,
            editor: {
                xtype: 'checkbox',
                cls: 'x-grid-checkheader-editor'
            }
        });
        columns.push( {
            xtype: 'checkcolumn',
            header: 'Add client info',
            dataIndex: 'client_info',
            width: 80,
            editor: {
                xtype: 'checkbox',
                cls: 'x-grid-checkheader-editor'
            }
        });
        columns.push( {
            header: 'Category',
            dataIndex: 'category',
            width: 130,
            field: {
                xtype: 'combobox',
                typeAhead: true,
                queryMode: 'local',
                selectOnTab: true,
                store: careerStore,
                displayField: 'name',
                listeners: {
                    change: function(Field, newValue, oldValue, options) {
                        careerStore.removeAll();
                        careerStoreArr = new Array();
                        careerStoreArr['- none -'] = true;
                        careerStore.add( {
                            "name": '- none -'
                        });
                        exportGrid.careerStore.each(careerStoreUpd);
                        var findExact = careerStore.findExact('name', newValue);
                        if (newValue && !careerStoreArr[newValue]) {
                            careerStore.add( {
                                "name": newValue
                            });
                        }
                    }
                },
                forceSelection: false,
                allowBlank: true
            }
        });
        columns[5].hidden = true;
    }

    // Grid
    gridPanel = {
        // title : title,
        store: exportGrid[key + 'Store'],
        plugins: [ cellEditing ],
        columns: columns,
        defaultSortable: false,
        height: 300,
        selModel: {
            selType: 'cellmodel'
        },
        dockedItems: [ {
            weight: 1,
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'footer',
            items: [ '->', {
                xtype: 'splitbutton',
                text: 'Cancel',
                menu: [ {
                    text: 'Reload data from user profile',
                    listeners: {
                        click: function() {
                            exportGrid[key + 'Store'].loadPage(999);
                        }
                    }
                } ],
                listeners: {
                    click: function() {
                        exportGrid[key + 'Store'].loadPage(1);
                    }
                }
            }, {
                xtype: 'button',
                text: 'Save profile',
                listeners: {
                    click: function() {
                        exportGrid[key + 'Store'].sync();
                    }
                }
            } ]
        } ]
    };

    if (key == 'career') {
        var groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: 'Category: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
            enableGroupingMenu: false
        });
        gridPanel.features = [ groupingFeature ];
    }

    return gridPanel;
}

var resumeExistingBuilds = function(uid) {
    resumeExistingBuildsStore = Ext.create('Ext.data.Store', {
        groupField: 'lang',
        fields: [ {
            name: 'id',
            type: 'int',
            useNull: true
        }, {
            name: 'name',
            type: 'string'
        }, {
            name: 'title',
            type: 'string'
        }, {
            name: 'created',
            type: 'string'
        }, {
            name: 'changed',
            type: 'string'
        }, {
            name: 'lang',
            type: 'string'
        }, {
            name: 'actions',
            type: 'string'
        } ],

        sorters: [ {
            property: 'id',
            direction: 'DESC'
        } ],

        proxy: {
            type: 'ajax',
            url: '/build/builds/uid/' + uid
        }
    });

    var fffff= 'fdgdfgd';
    var groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
        groupHeaderTpl: '{name}'
    });
    
    var resumeExistingBuildsGridPanel = Ext.create('Ext.grid.Panel', {
        store: resumeExistingBuildsStore,
        defaultSortable: true,
        features: [groupingFeature],
        height: 400,
        xtype: 'actioncolumn',
        columns: [ {
            text: "Id",
            width: 40,
            // hidden: true,
            dataIndex: 'id'
        }, {
            text: "Title",
            flex: 1,
            fixed: true,
            draggable: false,
            dataIndex: 'title'
        }, {
            text: "Created",
            hidden: true,
            width: 100,
            dataIndex: 'created'
        }, {
            text: "Changed",
            width: 100,
            dataIndex: 'changed'
        }, {
            text: "Lang",
            hidden: true,
            width: 50,
            dataIndex: 'lang'
        }, {
            text: "Download",
            width: 100,
            sortable: false,
            renderer: buildDownload,
            dataIndex: 'actions'
        }, {
            text: "Actions",
            width: 100,
            sortable: false,
            renderer: buildActions,
            dataIndex: 'actions'
        } ]
    });

    winExistingBuilds = Ext.createWidget('window', {
        width: 800,
        title: 'Existing Builds',
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: resumeExistingBuildsGridPanel
    }).show();
    resumeExistingBuildsStore.load();
}

var buildActions = function(val, p, r) {
    return '<a href="#edit" onclick="buildEditClick(' + r.data['id'] + ');" >Edit</a> | <a href="#delete" onclick="buildDeleteClick(' + r.data['id'] + ', \'' + r.data['title'] + '\');" >Delete</a>';
}

var buildDownload = function(val, p, r) {
    return '<a href="/build/export/type/pdf/id/' + r.data['id'] + '" target="_blank">PDF</a>, <a href="/build/export/type/rtf/id/' + r.data['id'] + '" target="_blank">RTF</a>';
}

var buildEditClick = function(id, title) {
    Mask.show();
    setTimeout("resumeExport(" + id + ", function(){ resumeExistingBuildsStore.load(); })", 50);
}

var buildExportClick = function(id, title) {
    $.jGrowl('buildExportClick' + 'id - ' + id + ', title - ' + title);
}

var buildDeleteClick = function(id, title) {
    Ext.MessageBox.confirm('Delete', 'Are you sure you want to delete <b>' + title + '</b>?', function(btn) {
        if (btn == 'yes') {
            Ext.Ajax.request( {
                url: '/build/delete/id/' + id,
                success: function(result, request) {
                    resumeExistingBuildsStore.load();
                },
                failure: function(result, request) {
                    resumeExistingBuildsStore.load();
                }
            });
        }
    });
}

var careerStoreArr = new Array();
var careerStoreUpd = function(record) {
    var category = record.get('category');
    if ($.trim(category) != '' && !careerStoreArr[category]) {
        careerStoreArr[category] = true;
        careerStore.add( {
            "name": category
        });
    }
}
