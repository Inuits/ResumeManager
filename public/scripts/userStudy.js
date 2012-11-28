cvData['globalProfile']['study'] = {
    sorters: [ {
        property: 'startDate',
        direction: 'DESC'
    } ],
    fields: [ {
        name: 'id',
        type: 'int',
        useNull: true
    }, {
        name: 'title',
        type: 'string'
    }, {
        name: 'title',
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
        name: 'location',
        type: 'string'
    }, {
        name: 'description',
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
        renderer: rendererTitle2,
        dataIndex: 'title',
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
        text: "Location",
        width: 100,
        dataIndex: 'location',
        field: {
            xtype: 'textfield'
        }
    }, {
        text: "Description",
        flex: 1,
        fixed: true,
        draggable: false,
        sortable: false,
        dataIndex: 'description',
        field: {
            height: 40,
            xtype: 'textareafield'
        }
    } ]
};

cvData['globalProfile']['training'] = cvData['globalProfile']['study'];
cvData['globalProfile']['education'] = cvData['globalProfile']['study'];

/**
 * Form
 */
cvData['globalProfile']['study']['form'] = function(title, key, tr) {

    var tabItems = new Array();
    tabItems[0] = studyFormFields(false, 'Original (en)');
    for ( var langKey in langs) {
        tabItems.push(studyFormFields(langKey, langs[langKey]));
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
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                            tables[key + 'Store'].load();
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

var studyFormFields = function(lang, title) {
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
        name: lang + 'title',
        xtype: 'textfield',
        anchor: '100%',
        allowBlank: false,
        disabled: translate,
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
            labelAlign: 'right',
            labelStyle: 'font-weight:normal',
            defaults: {
                flex: 1,
                hideLabel: 'true'
            },
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
            } ]
        });
    }

    items_data.push( {
        fieldLabel: 'Location',
        name: lang + 'location',
        xtype: 'textfield',
        disabled: translate,
        anchor: '100%',
        allowBlank: false,
        labelAlign: 'right',
        labelStyle: 'font-weight:normal'
    });

    items_data.push( {
        name: lang + 'description',
        xtype: 'textareafield',
        disabled: translate,
        anchor: '100%',
        height: 100,
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
        bodyPadding: 10,
        frame: true,
        fieldDefaults: {
            labelWidth: 70,
            msgTarget: 'qtip'
        },
        defaults: {
            msgTarget: 'side',
            margins: '0 0 10 0'
        },
        items: items_data
    };
}