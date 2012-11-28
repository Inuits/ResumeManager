cvData['globalProfile']['certification'] = {
    sorters: [ {
        property: 'startDate',
        direction: 'DESC'
    } ],
    fields: [ {
        name: 'id',
        type: 'int',
        useNull: true
    }, {
        name: 'name',
        type: 'string'
    }, {
        name: 'startDate',
        type: 'date',
        dateFormat: 'Y-m-d'
    }, {
        name: 'endDate',
        type: 'date',
        dateFormat: 'Y-m-d'
    }, {
        name: 'authority',
        type: 'string'
    }, {
        name: 'number',
        type: 'string'
    } ],

    columns: [ {
        text: "Id",
        width: 30,
        hidden: true,
        dataIndex: 'id'
    }, {
        text: "Title",
        width: 150,
        dataIndex: 'name',
        field: {
            xtype: 'textfield'
        }
    }, {
        text: "Start date",
        width: 100,
        dataIndex: 'startDate',
        renderer: dateFormat,
        field: {
            xtype: 'datefield',
            format: 'Y-m-d'
        }
    }, {
        text: "End date",
        width: 100,
        dataIndex: 'endDate',
        renderer: endDateFormat,
        field: {
            xtype: 'datefield',
            format: 'Y-m-d'
        }
    }, {
        text: "Authority",
        width: 100,
        dataIndex: 'authority',
        field: {
            xtype: 'textfield'
        }
    }, {
        text: "Number",
        flex: 1,
        fixed: true,
        draggable: false,
        sortable: false,
        dataIndex: 'number',
        field: {
            xtype: 'textfield'
        }
    } ]
};



/**
 * Form
 */

cvData['globalProfile']['certification']['form'] = function(title, key, tr) {

    var tabItems = new Array();
    tabItems[0] = certificationFormFields(false, 'Original (en)');
    for ( var langKey in langs) {
        tabItems.push(certificationFormFields(langKey, langs[langKey]));
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
                        url: '/ajax/save/uid/' + userId + '/type/' + key,
                        success: function(form, action) {
                            printMessages(action.result.messages);
                            tables[key + 'Store'].load();
                            customerStore.load();
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                            tables[key + 'Store'].load();
                            customerStore.load();
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
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: form,
        listeners: {
            afterrender: function() {
                if (tr) {
                    form.getForm().load( {
                        url: '/ajax/langload/id/' + tr.get('id') + '/type/' + key,
                        failure: function(form, action) {
                            Ext.Msg.alert("Load failed", action.result.errorMessage);
                        }
                    });
                }
            }
        }
    }).show();
}

var certificationFormFields = function(lang, title) {
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

    items_data.push( {
        fieldLabel: 'Title',
        name: lang + 'name',
        xtype: 'textfield',
        disabled: translate,
        anchor: '100%',
        allowBlank: false,
        labelAlign: 'right',
        labelStyle: 'font-weight:normal'
    });
    
    if (!translate) {
        items_data.push( {
            xtype: 'fieldcontainer',
            fieldLabel: 'Date',
            layout: 'hbox',
            combineErrors: true,
            defaultType: 'datefield',
            defaults: {
                flex: 1,
                hideLabel: 'true'
            },
            labelAlign: 'right',
            labelStyle: 'font-weight:normal',
            items: [ {
                name: 'startDate',
                fieldLabel: 'Start data',
                vtype: 'daterange',
                format: 'Y-m-d',
                emptyText: 'From',
                id: 'startDate',
                endDateField: 'endDate',
                allowBlank: false
            }, {
                name: 'endDate',
                fieldLabel: 'End data',
                vtype: 'daterange',
                format: 'Y-m-d',
                margins: '0 0 0 6',
                emptyText: 'To',
                id: 'endDate',
                startDateField: 'startDate',
                allowBlank: true
            }]
        });

        items_data.push( {
            fieldLabel: 'Number',
            name: 'number',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: true,
            labelAlign: 'right',
            labelStyle: 'font-weight:normal'
        });
    }

    items_data.push( {
        fieldLabel: 'Authority',
        name: lang + 'authority',
        xtype: 'textfield',
        disabled: translate,
        anchor: '100%',
        allowBlank: true,
        labelAlign: 'right',
        labelStyle: 'font-weight:normal'
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