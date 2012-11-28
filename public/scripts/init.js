/**
 * Vars
 */
var cvData = new Object();
var tables = new Object();
var Mask;
cvData['globalProfile'] = new Object();

var langs = {
    'nl': 'Dutch', 
    'fr': 'French'
};

// Stores
var customerStore;

Ext.Loader.setConfig({
    enabled: true
});
Ext.Loader.setPath('Ext.ux', '/scripts/ext/ux');
Ext.require([
    'Ext.ux.CheckColumn',
    'Ext.ux.TabScrollerMenu'
    ]);

// Ext.require([
// 'Ext.tab.*',
// 'Ext.tree.*',
// 'Ext.tip.*',
// 'Ext.window.*',
// 'Ext.form.*',
// 'Ext.layout.*',
// 'Ext.grid.*',
// 'Ext.data.*',
// 'Ext.util.*',
// 'Ext.state.*',
// 'Ext.container.Viewport',
// 'Ext.selection.*'
// ]);


/*******************************************************************************
 * Helpr
 ******************************************************************************/

function print_r( array, return_val ) {
    var output = "", pad_char = " ", pad_val = 4;
    var formatArray = function (obj, cur_depth, pad_val, pad_char) {
        if(cur_depth > 0)
            cur_depth++;
 
        var base_pad = repeat_char(pad_val*cur_depth, pad_char);
        var thick_pad = repeat_char(pad_val*(cur_depth+1), pad_char);
        var str = "";
 
        if(obj instanceof Array || obj instanceof Object) {
            str += "Array\n" + base_pad + "(\n";
            for(var key in obj) {
                if(obj[key] instanceof Array || obj[key] instanceof Object) {
                    str += thick_pad + "["+key+"] => "+formatArray(obj[key], cur_depth+1, pad_val, pad_char);
                } else {
                    str += thick_pad + "["+key+"] => " + obj[key] + "\n";
                }
            }
            str += base_pad + ")\n";
        } else {
            str = obj.toString();
        };
 
        return str;
    };
 
    var repeat_char = function (len, char1) {
        var str = "";
        for(var i=0; i < len; i++) {
            str += char1;
        };
        return str;
    };
 
    output = formatArray(array, 0, pad_val, pad_char);
 
    if(return_val !== true) {
        document.write("<pre>" + output + "</pre>");
        return true;
    } else {
        return output;
    }
}


/**
 * printMessages
 */
var printMessages = function (messages) {
    $.jGrowl.defaults.life = 15000;
    if (messages) {
        for (var key in messages) {
            $.jGrowl(messages[key]['body'], {
                header : messages[key]['title'],
                theme : messages[key]['theme'],
                sticky : messages[key]['sticky']
            });
        }
    }
}

/**
 * rendererLink
 * 
 * @param val
 * @returns
 */
var linkFormat = function (val) {
    if (val) {
        return '<a href="'+ val +'">'+ val +'</a>';
    }
    return val;
}

/**
 * dataRange
 * 
 * @param val
 * @param p
 * @param r
 * @returns
 */
var dataRange = function (val, p, r) {
    var f = Ext.util.Format.date;
    var out = f(r.data['startDate'], 'Y-m-d');
    if (f(r.data['startDate'], 'Y-m-d') == f(r.data['endDate'], 'Y-m-d')) {
        return out;
    }
    
    if (r.data['endDate']) {
        out += ' to ';
        out += f(r.data['endDate'], 'Y-m-d');
    }
    else {
        out += ' - Present ';
    }
    return out;
}
/**
 * nameRange
 * 
 * @param val
 * @param p
 * @param r
 * @returns
 */
var nameRenderer = function (val, p, r) {
    if (r.data['lastName'] && r.data['firstName']) {
        var out = r.data['lastName'] + '  ' + r.data['firstName'];
    }
    else {
        var out = r.data['userLogin'];
    }
    out = '<a href="/user/view/uid/' + r.data['id'] + '">' + out + '</a>';
    return out;
}

/**
 * 
 * @param val
 * @returns
 */
var rendererTitle = function (val) {
    return '<b>' + val + ':</b>';
}


/**
 * 
 * @param val
 * @returns
 */
var rendererTitle2 = function (val) {
    return '<b>' + val + '</b>';
}


/**
 * endDateFormat
 * 
 * @param val
 * @param p
 * @param r
 * @returns
 */
var endDateFormat = function (val, p, r) {
    var f = Ext.util.Format.date;
    if (val) {
        return f(val, 'Y-m-d');
    }
    else if (r.data['startDate']) {
        return 'Present';
    }
    else {
        return '';
    }
}

/**
 * dateFormat
 * 
 * @param val
 * @returns
 */
var dateFormat = function (val) {
    var f = Ext.util.Format.date;
    if (val) {
        return f(val, 'Y-m-d');
    }
}

/**
 * Clone object
 * 
 * @param obj
 * @returns {___c0}
 */
function clone(obj) {
    function Clone() { } 
    Clone.prototype = obj;
    var c = new Clone();
    c.constructor = Clone;
    return c;
}



/*******************************************************************************
 * Default
 ******************************************************************************/


var userProfileGetSelectionModel = function(key) {
    tables[key].getSelectionModel().on('selectionchange', function(selModel, selections){
        tables['selected' + key] = selections[0];
        tables['selectedAll' + key] = selections;
        tables[key].down('#edit').setDisabled((selections.length === 0 || selections.length > 1));
        tables[key].down('#delete').setDisabled(selections.length === 0);
    });
}


var userProfileDataStore = function(key, store_key) {
    return {
        autoLoad: true,
        autoSync: true,
        fields : cvData['globalProfile'][store_key]['fields'],
        sorters: cvData['globalProfile'][store_key]['sorters'],
        proxy : {
            type: 'ajax',
            url : '/ajax/userload/uid/' + userId + '/type/' + key,
            reader : {
                type : 'json',
                root: 'data'
            },
            writer: {
                type: 'json'
            }
        }
    };
}

var userProfileGridPanel = function(key, title, store_key) {
    
    $('<div class="user_profile_table" id="user_profile_'+ key +'"><h3>'+ title +'</h3></div>').appendTo('#user_profile');
    var userProfileGridPanel = new Object();
    
    // Grid
    userProfileGridPanel = {
        // title : title,
        store : tables[key + 'Store'],
        columns : cvData['globalProfile'][store_key]['columns'],
        defaultSortable: true,
        multiSelect: true,
        xtype: 'actioncolumn',
        stateful: true,
        id: 'userProfileGridPanel_' + key,
        stateId: 'userProfileGridPanel_' + key,
        viewConfig: {
            stripeRows: true
        },
        renderTo: 'user_profile_' + key
    };
    
    // Top panel
    userProfileGridPanel.dockedItems = [{
        xtype: 'toolbar',
        items: [{
            text: 'New',
            iconCls: 'icon-add',
            handler: function(){
                cvData['globalProfile'][store_key]['form']('Add ' + title, key);
            }
        }, '-', {
            itemId: 'edit',
            text: 'Edit',
            iconCls: 'icon-edit',
            disabled: true,
            handler: function(){
                cvData['globalProfile'][store_key]['form']('#' + tables['selected' + key].get('id') +' '+ title, key, tables['selected' + key]);
            }
        }, '-', {
            itemId: 'delete',
            text: 'Delete',
            iconCls: 'icon-delete',
            disabled: true,
            handler: function(){
                var titles = '';
                var id_list = '';
                for (var selectedKey in tables['selectedAll' + key]) {
                    if (id_list) {
                        id_list += ', ';
                        titles += ', ';
                    }
                    id_list += tables['selectedAll' + key][selectedKey].getId();
                    if (tables['selectedAll' + key][selectedKey].get('title')) {
                        titles += tables['selectedAll' + key][selectedKey].get('title'); 
                    }
                    else if (tables['selectedAll' + key][selectedKey].get('name')) {
                        titles += tables['selectedAll' + key][selectedKey].get('name');
                    }
                    else {
                        titles += '#' + tables['selectedAll' + key][selectedKey].getId();
                    }
                    
                }
            
                Ext.MessageBox.confirm('Delete', 'Are you sure you want to delete <b>'+ titles +'</b>?', function(btn){
                    if(btn == 'yes'){
                        Ext.Ajax.request({
                            url: '/ajax/delete',
                            success: function(result, request) {
                                var jsonData = Ext.decode(result.responseText);
                                printMessages(jsonData.messages);
                                tables[key + 'Store'].load();
                            },
                            failure: function(result, request) {
                                var jsonData = Ext.decode(result.responseText);
                                printMessages(jsonData.messages);
                                tables[key + 'Store'].load();
                            },
                            params: { 
                                type: key,
                                uid: userId,
                                idList: id_list
                            }
                        });
                    }
                });
            }
        }]
    }];
    
    // Listeners
    userProfileGridPanel.listeners = {
        'itemdblclick' : function(view , record, item, index) {
            tables['selected' + key] = record;
            cvData['globalProfile'][store_key]['form']('#' + record.get('id') +' '+ title, key, record);
        }
    };
    
    return userProfileGridPanel;
}


var userPersonalInfo = function() {
    $.get('/ajax/userinfo/uid/' + userId, 
        function (data) {
            $('#personal_info').html(data);
        }
        );
}


var userToolBar = function() {
    new Ext.Button({
        text: 'Edit profile',
        scale: 'medium',
        margin: 5,
        iconCls: 'user',
        listeners: {
            click: function () {
                cvData.globalProfile.userProfileForm();
            }
        },
        renderTo: 'userToolBar'
    });
    
    new Ext.Button({
        text: 'Import',
        scale: 'medium',
        margin: 5,
        iconCls: 'card-import',
        listeners: {
            click: function () {
                cvData.globalProfile.userResumeImport();
            //                Ext.MessageBox.confirm('Clear current CV', 'Are you sure you want to clear current CV and load it from LinkedIn?', function(btn) {
            //                    if (btn == 'yes') {
            //                        cvData.globalProfile.userResumeImport();
            //                    }
            //                });
            }
        },
        renderTo: 'userToolBar'
    });
    
    new Ext.SplitButton({
    // new Ext.Button({
        text: 'Build resume',
        xtype: 'splitbutton',
        // xtype: 'button',
        scale: 'medium',
        iconCls: 'icon-wand',
        margin: 5,
        menu: [
//            {
//            text: 'New resume',
//            listeners: {
//                click: function () {
//                    resumeExportSelectLang(userId);
//                }
//            }
//        }, 
        {
            text: 'Existing builds',
            listeners: {
                click: function () {
                    resumeExistingBuilds(userId);
                }
            }
        }],
        listeners: {
            click: function () {
                resumeExportSelectLang(userId);
            }
        },
        renderTo: 'userToolBar'
    });
};


var userLoginForm = function() {
    Ext.QuickTips.init();

    var login = Ext.create('Ext.form.Panel', { 
        title: 'Login',
        labelWidth: 80,
        width: 330,
        fieldDefaults: {
            labelAlign: 'left',
            labelWidth: 100,
            width: 290,
            msgTarget: 'qtip'
        },
        url: '/ajax/login',
        defaultType: 'textfield',
        monitorValid: true,
        bodyStyle: 'padding: 20px;',
        items:[{ 
            fieldLabel:'Username', 
            name:'login',
            value: '', 
            allowBlank:false 
        }, { 
            fieldLabel: 'Password', 
            name: 'password', 
            inputType: 'password', 
            value: '',
            allowBlank: false 
        }, {
            xtype: 'container',
            style: 'text-align:right',
            items: [{
                xtype: 'button',
                text: 'Login',
                handler:function(){ 
                    login.getForm().submit({ 
                        method:'POST', 
                        waitTitle:'Please wait', 
                        waitMsg:'Sending data...',
     
                        success:function(form, action){
                            window.location = '/user/view/uid/' + action.result.uid;
                        },
                        failure:function(form, action){ 
                            printMessages(action.result.messages);
                        } 
                    }); 
                } 
            }]
        }]
    });
    
    var registration = Ext.create('Ext.form.Panel', { 
        title: 'Registration',
        disabled: true,
        fieldDefaults: {
            labelAlign: 'left',
            labelWidth: 120,
            width: 290,
            msgTarget: 'qtip'
        },
        width: 330,
        url:'/ajax/registration',
        defaultType:'textfield',
        monitorValid:true,
        bodyStyle:'padding: 20px',
        items:[{ 
            fieldLabel:'Username', 
            name:'login', 
            allowBlank:false 
        },{ 
            fieldLabel:'Password', 
            name:'password', 
            inputType:'password',
            id: 'userpass',
            allowBlank:false
        }, {
            fieldLabel: 'Confirm Password',
            name: 'password-cfrm',
            inputType:'password', 
            vtype: 'password',
            initialPassField: 'userpass',
            allowBlank:false 
        }, {
            xtype: 'container',
            style: 'text-align:right',
            items: [{
                xtype: 'button',
                text: 'Send',
                handler:function(){ 
                    registration.getForm().submit({ 
                        method:'POST', 
                        waitTitle:'Please wait', 
                        waitMsg:'Sending data...',
     
                        success:function(form, action){
                            window.location = '/user/view/uid/' + action.result.uid;
                        },
                        failure:function(form, action){ 
                            printMessages(action.result.messages);
                        } 
                    }); 
                } 
            }]
        }]
    });
    // second tabs built from JS
    var tabs2 = Ext.createWidget('tabpanel', {
        renderTo: 'content',
        activeTab: 0,
        plain: true,
        items: [login, registration]
    });
}


Ext.onReady(function () {
    Mask = new Ext.LoadMask(Ext.getBody(), {
        msg:"Please wait..."
    });
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    
    Ext.apply(Ext.form.field.VTypes, {
        daterange: function (val, field) {
            var date = field.parseDate(val);

            if (!date) {
                return false;
            }
            if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
                var start = field.up('form').down('#' + field.startDateField);
                start.setMaxValue(date);
                start.validate();
                this.dateRangeMax = date;
            } else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
                var end = field.up('form').down('#' + field.endDateField);
                end.setMinValue(date);
                end.validate();
                this.dateRangeMin = date;
            }
            return true;
        },

        daterangeText: 'Start date must be less than end date',

        password: function (val, field) {
            if (field.initialPassField) {
                var pwd = field.up('form').down('#' + field.initialPassField);
                return (val == pwd.getValue());
            }
            return true;
        },

        passwordText: 'Passwords do not match'
    });

    customerStore = Ext.create('Ext.data.Store', {
        fields: [{
            name: 'id',
            type: 'int',
            useNull: true
        }, {
            name: 'name',
            type: 'string'
        }],
        proxy: {
            type: 'ajax',
            url: '/ajax/customerlist/type/small',
            reader: {
                type: 'json',
                root: 'data'
            },
            writer: {
                type: 'json'
            }
        }
    });
});


var enableLangCheckbox = function (lang, title) {
    return {
        xtype: 'checkbox',
        name: lang + 'enable_lang',
        boxLabel: 'Enable',
        labelAlign: 'left',
        style: 'margin-left: 400px',
        handler: function(me, checked) {
            var owner = me.ownerCt;
            owner.setTitle(checked ? title + ' [+]' : title + ' [-]');
            Ext.Array.forEach(owner.query('textfield'), function(field) {
                field.setDisabled(!checked);
                field.el.animate( {
                    opacity: !checked ? .4 : 1
                });
            });
        }
    };
}

