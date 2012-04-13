/* Main function */
Ext.onReady(function(){
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    Ext.BLANK_IMAGE_URL = './img/s.gif';
    Ext.QuickTips.init();
    Ext.Direct.addProvider(Ext.app.REMOTING_API);
    
    /* Error handler.
       It will catch exception from server */
    Ext.Direct.on('exception',function(responce){
        Ext.Msg.show({
            title:'Error',
            msg:responce.message,
            buttons:Ext.Msg.OK,
            icon:Ext.Msg.ERROR
        });
    });
    //Model of grid's columns
    Ext.define('PacketInfo', {
        extend: 'Ext.data.Model',
        fields: [
            {name:'no'},
            {name:'time'},
            {name:'source'},
            {name:'destination'},
            {name:'protocol'},
            {name:'info'}
        ],
        idProperty: 'no'
    });
    //Main element of page
    var viewport = new Ext.Viewport({
        layout:'border',
        items:[{
            region:'north',
            tbar:Ext.Toolbar({ //Head toolbar
                items:[{
                    xtype:'button',
                    itemId:'openFile',
                    iconCls:'script-binary',
                    text:'Open',
                    scope:this,
                    handler:function(){
                        new openFileDialog();
                    }
                },{xtype: 'tbfill'},'Filter:',{
                    xtype:'textfield',
                    id:'filter_field',
                    width:300,
                    name:'filter',
                },{
                    xtype:'button',
                    iconCls:'treearrow',
                    handler:function(){
                        packetListStore.getProxy().extraParams['filter']=Ext.getCmp('filter_field').getValue();
                        packetListStore.load();
                    }
                }]
            }),
            collapsible: false,
            height:28,
            margins:'5 5 5 5'
        },{
            region:'center', //Center element with main table
            margins:'0 5 5 5',
            height:'600',
            border:0,
            items:[{
                xtype:'grid',
                id:'packetListGrid',
                listeners:{
                    /* Dbl click handler */
                    itemdblclick:function(v,r,item,index,e){
                        new viewPacketWindow({no:r.data.no,file:packetListStore.getProxy().extraParams['file']});
                    },
                    scope:this
                },
                /* List of packets getting by this store */
                store:packetListStore=new Ext.data.DirectStore({
                    storeId:'packetList',
                    paramsAsHash:true,
                    listeners:{
                       load:function(){Ext.getCmp('packetListGrid').invalidateScroller();},
                       scope:this
                    },
                    pageSize: 40,
                    buffered: true,
                    proxy:{
                        type: 'direct',
                        directFn:TShark.loadGrid, //Remote function
                        reader:{
                            totalProperty: 'total',
                            root: 'data'
                        }
                    },
                    autoLoad: false,
                    model:'PacketInfo',
                    remoteSort: true
                }),
                bbar:{
                    xtype:'pagingtoolbar',
                    pageSize: 200,
                    store: packetListStore,
                    inputItemWidth:60,
                    displayInfo: true
                },
                loadMask: true,
                viewConfig: {
                    trackOver: false //I don't know why it here
                },
                //List of columns
                columns:[{
                    header: '№',
                    dataIndex: 'no'
                },{
                    header: 'Time',
                    dataIndex: 'time'
                },{
                    header: 'Source',
                    dataIndex: 'source'
                },{
                    header: 'Destination',
                    dataIndex: 'destination'
                },{
                    header: 'Protocol',
                    dataIndex: 'protocol'
                },{
                    header: 'Info',
                    dataIndex: 'info',
                    flex:1
                }],
                height: '100%',
                width: '100%'
            }]
        }]
    });
});