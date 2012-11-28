var customerListStore;
var customerList;
cvData['globalProfile']['customer'] = new Array();

var customerListGridPanel = function(title) {
    Ext.QuickTips.init();
    var tableTitle = '';
    var customerListGridPanel = new Object();

    if (title) {
        var tableTitle = '<h2>' + title + '</h2>';
    }
    $('<div class="customer_list_table" id="customer_list">' + tableTitle + '</div>').appendTo('#content');

    customerListGridPanel = {
        store: tables['customerStore'],
        defaultSortable: true,
        multiSelect: true,
        xtype: 'actioncolumn',
        stateful: true,
        stateId: 'customerListGridPanel',
        id: 'customerListGridPanel',
        renderTo: 'customer_list',
        columns: [ {
            text: "Id",
            width: 30,
            hidden: true,
            dataIndex: 'id'
        }, {
            text: "Name",
            width: 150,
            renderer: rendererTitle2,
            dataIndex: 'name'
        }, {
            text: "Description",
            flex: 1,
            fixed: true,
            draggable: false,
            sortable: false,
            dataIndex: 'description'
        } ]
    };

    // Top panel
    customerListGridPanel.dockedItems = [ {
        xtype: 'toolbar',
        items: [ {
            text: 'Add customer',
            iconCls: 'icon-add',
            handler: function() {
                cvData['globalProfile']['customer']['form']('Add customer');
            }
        }, '-', {
            itemId: 'edit',
            text: 'Edit',
            iconCls: 'icon-edit',
            disabled: true,
            handler: function() {
                cvData['globalProfile']['customer']['form']('Edit customer', tables['selectedcustomer']);

            }
        }, '-', {
            itemId: 'delete',
            text: 'Delete',
            iconCls: 'icon-delete',
            disabled: true,
            handler: function() {
                var titles = '';
                var id_list = '';
                for ( var selectedKey in tables['selectedAllcustomer']) {
                    if (id_list) {
                        id_list += ', ';
                        titles += ', ';
                    }
                    id_list += tables['selectedAllcustomer'][selectedKey].getId();
                    titles += tables['selectedAllcustomer'][selectedKey].get('name');

                }

                Ext.MessageBox.confirm('Delete', 'Are you sure you want to delete <b>' + titles + '</b>?', function(btn) {
                    if (btn == 'yes') {
                        Ext.Ajax.request( {
                            url: '/ajax/delete',
                            success: function(result, request) {
                                var jsonData = Ext.decode(result.responseText);
                                printMessages(jsonData.messages);
                                tables['customerStore'].load();
                            },
                            failure: function(result, request) {
                                var jsonData = Ext.decode(result.responseText);
                                printMessages(jsonData.messages);
                                tables['customerStore'].load();
                            },
                            params: {
                                type: 'customer',
                                idList: id_list
                            }
                        });
                    }
                });
            }
        } ]
    } ];

    // Listeners
    customerListGridPanel.listeners = {
        'itemdblclick': function(view, record, item, index) {
            tables['selectedcustomer'] = record;
            cvData['globalProfile']['customer']['form']('Edit customer', record);
        }
    };

    return customerListGridPanel;
}

/**
 * Store
 */
var customerListStoreData = {
    autoLoad: true,

    fields: [ {
        name: 'id',
        type: 'int',
        useNull: true
    }, {
        name: 'name',
        type: 'string'
    }, {
        name: 'description',
        type: 'string'
    } ],

    sorters: [ {
        property: 'name',
        direction: 'DESC'
    } ],

    proxy: {
        type: 'ajax',
        url: '/ajax/customerlist',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
};

/**
 * Form
 */
cvData['globalProfile']['customer']['form'] = function(title, tr) {
    var tabItems = new Array();
    tabItems[0] = customerFormFields(false, 'Original (en)');
    for ( var langKey in langs) {
        tabItems.push(customerFormFields(langKey, langs[langKey]));
    }

    var tabs = Ext.create('Ext.tab.Panel', {
        resizeTabs: true,
        enableTabScroll: true,
        defaults: {
            closable: false
        },
        items: tabItems
    });

    var form = Ext.widget('form', {
        items: tabs,
        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('form').getForm().reset();
                this.up('window').hide();
            }
        }, {
            text: 'Save',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/save/type/customer',
                        success: function(form, action) {
                            printMessages(action.result.messages);
                            tables['customerStore'].load();
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                            tables['customerStore'].load();
                        }
                    });
                    this.up('window').hide();
                }
            }
        } ]
    });

    if (tr) {
        form.loadRecord(tr);
    }

    Ext.widget('window', {
        title: title,
        width: 500,
        layout: 'anchor',
        resizable: false,
        border: false,
        modal: true,
        items: form,
        listeners: {
            afterrender: function() {
                if (tr) {
                    form.getForm().load( {
                        url: '/ajax/langload/id/' + tr.get('id') + '/type/customer',
                        failure: function(form, action) {
                            Ext.Msg.alert("Load failed", action.result.errorMessage);
                        }
                    });
                }
            }
        }
    }).show();
}

var customerFormFields = function(lang, title) {
    var translate = false;
    if (lang) {
        lang = lang + ':';
        translate = true;
    } else {
        lang = '';
    }
    var items_data = [];

    if (translate) {
        items_data.push(enableLangCheckbox(lang, title));
    }

    if (!translate) {
        items_data.push( {
            name: 'name',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: false
        });
    }

    items_data.push( {
        name: lang + 'description',
        xtype: 'textareafield',
        anchor: '100%',
        height: 100,
        disabled: translate
    });

    if (!translate) {
        items_data.push( {
            xtype: 'hidden',
            name: 'id'
        });
    }

    return formFields = {
        title: translate ? title + ' [-]' : title,
        layout: 'anchor',
        border: false,
        defaultType: 'textfield',
        bodyPadding: 10,
        frame: true,
        fieldDefaults: {
            msgTarget: 'qtip'
        },
        defaults: {
            msgTarget: 'side',
            margins: '0 0 10 0'
        },
        items: items_data
    };
}
