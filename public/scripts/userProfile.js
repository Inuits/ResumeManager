userProfileFormStores = new Array();

// var userProsileStoresLoad = function(key) {
// if (userProfileFormStores[key]) {
// userProfileFormStores[key].load();
// } else {
// userProfileFormStores[key] = Ext.create('Ext.data.Store', {
// fields: [ {
// name: 'key',
// type: 'string'
// } ],
// proxy: {
// type: 'ajax',
// url: '/ajax/userstore/type/' + key,
// reader: {
// type: 'json',
// root: 'data'
// }
// }
// }).load();
// }
// };

var linkedInStatusUpd = function(jsonData) {
    if (jsonData.success) {
        var profileBtn = Ext.getCmp('linkedin_profile');
        profileBtn.setText('You are login as ' +jsonData.linkedin['first-name'] + ' ' + jsonData.linkedin['last-name']);
        profileBtn.setDisabled(true);
        
        var importBtn = Ext.getCmp('linkedin_import');
        importBtn.setDisabled(false);
    }
}
/**
 * Forms
 */
cvData['globalProfile']['userResumeImport'] = function() {
    var win;
    var tabItems = [];
    /*
    tabItems[1] = Ext.widget('form', {
        title: 'Get from public url',
        layout: 'anchor',
        border: false,
        bodyPadding: '10px',
        defaultType: 'textfield',
        frame: true,
        fieldDefaults: {
            labelAlign: 'top',
            labelStyle: 'font-weight:normal',
            labelWidth: 120
        },
        items: [ {
            fieldLabel: 'LinkedIn profile',
            name: 'url',
            vtype: 'url',
            value: '',
            emptyText: 'http://www.linkedin.com/in/',
            anchor: '100%',
            allowBlank: false
        } ],

        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('window').hide();
            }
        }, {
            text: 'Import',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/resumeimport/uid/' + userId,
                        waitTitle: 'Please wait',
                        waitMsg: 'Import in process...',
                        success: function(form, action) {
                            win.hide();
                            if (action.result.messages) {
                                printMessages(action.result.messages);
                            }
                            cvData.globalProfile.skillsMapper(action.result.skills);
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                        }
                    });
                }
            }
        } ]
    });
   */

    tabItems[0] = Ext.widget('form', {
//        title: 'Get from profile',
        layout: 'anchor',
        border: false,
        bodyPadding: '20px 10px',
        defaultType: 'button',
        frame: true,
        fieldDefaults: {
            labelAlign: 'top',
            labelStyle: 'font-weight:normal',
            labelWidth: 120
        },
        items: [ {
            xtype: 'fieldset',
            flex: 1,
            title: 'Step 1: Authentication',
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                hideEmptyLabel: false
            },
            items: [{
            text: 'LinkedIn authorization',
            xtype: 'button',
            id: 'linkedin_profile',
            scale: 'medium',
            iconCls: 'linkedin',
            listeners: {
                click: function() {
                    window.open('/ajax/linkedin/uid/' + userId, "authorization", "menubar=0,resizable=1,width=450,height=250");
                }
            }
        }]
        }, {
            xtype: 'fieldset',
            margin: '15px 0px 0px 0px',
            flex: 1,
            title: 'Step 2: Select types of data to import (old data will be deleted)',
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                hideEmptyLabel: false
            },
            items: [{
            xtype: 'checkboxgroup',
            columns: 1,
            items: [
                {boxLabel: 'Profile', name: 'profile', checked: true},
                {boxLabel: 'Skills', name: 'skills', checked: true},
                {boxLabel: 'Career history', name: 'career', checked: true},
                {boxLabel: 'Training', name: 'training', checked: true},
                {boxLabel: 'Education', name: 'education', checked: true},
                {boxLabel: 'Publication', name: 'publication', checked: true},
                {boxLabel: 'Talk', name: 'talk', checked: true},
                {boxLabel: 'Certification', name: 'certification', checked: true},
                {boxLabel: 'Project', name: 'project', checked: true} 
            ]
        }]
        } ],

        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('window').hide();
            }
        }, {
            text: 'Import',
            id: 'linkedin_import',
            disabled: true,
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/linkedinimport/uid/' + userId,
                        waitTitle: 'Please wait',
                        waitMsg: 'Import in process...',
                        success: function(form, action) {
                            win.hide();
                            if (action.result.messages) {
                                printMessages(action.result.messages);
                            }
                            cvData.globalProfile.skillsMapper(action.result.skills);
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                        }
                    });
                }
            }
        } ]
    });
    /*
    var tabs = Ext.create('Ext.tab.Panel', {
        resizeTabs: true,
        enableTabScroll: true,
        defaults: {
            closable: false
        },
        items: tabItems
    });
    */

    win = Ext.widget('window', {
        iconCls: 'card-import',
        title: 'Resume import',
        width: 450,
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: tabItems[0]
    }).show();
}

cvData['globalProfile']['skillsMapper'] = function(skills) {
    if (!skills.data[0]) {
        window.location.reload();
        return false;
    }

    var skillCats = Ext.create('Ext.data.Store', {
        fields: [ 'cat' ],
        data: skills.cats
    });

    var skills_form = [];
    for ( var key in skills.data) {
        skills_form.push( {
            xtype: 'combo',
            mode: 'local',
            value: skills.data[key].cat,
            triggerAction: 'all',
            forceSelection: true,
            editable: false,
            allowBlank: false,
            fieldLabel: skills.data[key].skill,
            name: skills.data[key].skill,
            displayField: 'cat',
            valueField: 'cat',
            queryMode: 'local',
            anchor: '100%',
            store: skillCats
        })
    }

    var form = Ext.widget('form', {
        layout: 'anchor',
        border: false,
        bodyPadding: '20px 10px 20px 20px',
        defaultType: 'textfield',
        frame: true,
        fieldDefaults: {
            labelAlign: 'left',
            labelStyle: 'font-weight:normal',
            labelWidth: 120
        },
        items: skills_form,

        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('window').hide();
                window.location.reload();
            }
        }, {
            text: 'Update skills',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/skillmapper/uid/' + userId,
                        waitTitle: 'Please wait',
                        success: function(form, action) {
                            window.location.reload();
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

    Ext.widget('window', {
        iconCls: 'card-import',
        title: 'Skills mapper',
        width: 450,
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: form
    }).show();
}

cvData['globalProfile']['userProfileForm'] = function(tr) {

    var tabItems = new Array();
    tabItems[0] = userProfileFormFields(false, 'Original (en)');
    for ( var langKey in langs) {
        tabItems.push(userProfileFormFields(langKey, langs[langKey]));
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
                    var form = this.up('form').getForm();

                    this.up('window').hide();
                    Ext.Ajax.request( {
                        method: 'POST',
                        url: '/ajax/save/type/profile',
                        params: form.getValues(),
                        success: function(response, action) {
                            var response = Ext.decode(response.responseText);
                            printMessages(response.messages);
                            userPersonalInfo();
                            // userProsileStoresLoad('nationality');
                            // userProsileStoresLoad('language');
                            // userProsileStoresLoad('location');
                        },
                        failure: function(form, action) {
                            var response = Ext.decode(response.responseText);
                            printMessages(response.messages);
                            userPersonalInfo();
                        }
                    });
                }
            }
        } ]
    });

    Mask.show();
    Ext.widget('window', {
        title: 'Edit profile',
        width: 550,
        layout: 'fit',
        resizable: false,
        border: false,
        modal: true,
        items: form,
        listeners: {
            afterrender: function() {
                form.getForm().load( {
                    url: '/ajax/userinfo/uid/' + userId + '/json/1',
                    success: function(response, action) {
                        Mask.hide();
                    },
                    failure: function(form, action) {
                        Mask.hide();
                        Ext.Msg.alert("Load failed", action.result.errorMessage);
                    }
                });
            }
        }
    }).show();
}

var userProfileFormFields = function(lang, title) {
    var translate = false;
    if (lang) {
        lang = lang + ':';
        translate = true;
    } else {
        lang = '';
    }
    var empty_field = {
        xtype: 'hidden',
        value: '',
        name: 'empty'
    };

    var items_data = [ empty_field, {
        xtype: 'fieldset',
        title: 'Personal Information',
        defaultType: 'textfield',
        layout: 'anchor',
        defaults: {
            anchor: '100%'
        },
        items: [ {
            xtype: 'fieldcontainer',
            layout: 'hbox',
            combineErrors: true,
            defaultType: 'textfield',
            fieldDefaults: {
                labelAlign: 'top',
                labelStyle: 'font-weight:normal'
            },
            items: [ {
                emptyText: 'First',
                flex: 2,
                fieldLabel: 'Name',
                labelAlign: 'right',
                labelWidth: 90,
                labelStyle: 'font-weight:normal',
                name: lang + 'firstName',
                disabled: translate
            }, {
                emptyText: 'Last',
                margins: '0 0 0 6',
                flex: 3,
                name: lang + 'lastName',
                disabled: translate
            } ]
        }, {
            xtype: 'fieldcontainer',
            layout: 'hbox',
            combineErrors: true,
            defaultType: 'textfield',
            fieldDefaults: {
                labelAlign: 'right',
                labelWidth: 90,
                labelStyle: 'font-weight:normal'
            },
            items: [ {
                fieldLabel: 'Place of Birth',
                flex: 3,
                name: lang + 'birthPlace',
                disabled: translate
            }, {
                fieldLabel: 'Date of Birth',
                name: lang + 'birthDate',
                format: 'Y-m-d',
                flex: 2,
                xtype: 'datefield',
                disabled: translate
            } ]
        } ]
    }, {
        xtype: 'fieldset',
        title: 'Additional Information',
        defaultType: 'textfield',
        layout: 'anchor',
        defaults: {
            anchor: '100%'
        },
        items: [ {
            fieldLabel: 'Location',
            name: lang + 'location',
            xtype: 'textfield',
            // xtype: 'combobox',
            // store: userProfileFormStores.location,
            // valueField: 'key',
            // displayField: 'key',
            // typeAhead: true,
            // queryMode: 'local',
            // forceSelection: false,
            // allowBlank: true,
            labelAlign: 'right',
            labelWidth: 90,
            labelStyle: 'font-weight:normal',
            disabled: translate
        }, {
            fieldLabel: 'Language',
            name: lang + 'lang',
            xtype: 'textfield',
            // xtype: 'combobox',
            // store: userProfileFormStores.language,
            // valueField: 'key',
            // displayField: 'key',
            // typeAhead: true,
            // queryMode: 'local',
            // forceSelection: false,
            // allowBlank: true,
            labelAlign: 'right',
            labelWidth: 90,
            labelStyle: 'font-weight:normal',
            disabled: translate
        }, {
            fieldLabel: 'Nationality',
            name: lang + 'nationality',
            margins: '0 0 0 6',
            xtype: 'textfield',
            // xtype: 'combobox',
            // store: userProfileFormStores.nationality,
            // valueField: 'key',
            // displayField: 'key',
            // typeAhead: true,
            // queryMode: 'local',
            // forceSelection: false,
            // allowBlank: true,
            labelAlign: 'right',
            labelWidth: 90,
            labelStyle: 'font-weight:normal',
            disabled: translate
        }, {
            xtype: 'fieldcontainer',
            layout: 'hbox',
            combineErrors: true,
            defaultType: 'textfield',
            defaults: {
                flex: 1
            },
            fieldDefaults: {
                labelAlign: 'right',
                labelWidth: 90,
                labelStyle: 'font-weight:normal'
            },
            items: [ {
                fieldLabel: 'Company',
                name: lang + 'company',
                allowBlank: true,
                disabled: translate
            }, {
                fieldLabel: 'Social security',
                emptyText: 'xxx-xx-xxxx',
                margins: '0 0 0 6',
                name: lang + 'socialSecurity',
                allowBlank: true,
                disabled: translate
            } ]
        } ]
    }, {
        name: lang + 'profile',
        xtype: 'textareafield',
        anchor: '100%',
        height: 100,
        disabled: translate
    }, {
        xtype: 'hidden',
        value: userId,
        name: lang + 'uid'
    } ];

    if (translate) {
        items_data[0] = enableLangCheckbox(lang, title);
        items_data[0].style = 'margin-left: 450px';
        items_data[1]['items'][1]['items'][1] = empty_field;
        items_data[2]['items'][3]['items'][1] = empty_field;
        items_data[4] = empty_field;
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
