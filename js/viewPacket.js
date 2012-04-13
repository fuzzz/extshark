/* This function create window with packet detailed info */
function viewPacketWindow (data){
	this.data=data;
	this.title="View detailed info. Packet";
	if(this.data){
		if(this.data.title){
			this.title=this.data.title;
		}
		if(this.data.no){
			this.no=this.data.no;
			this.title=this.title+' #'+this.no;
		}
		if(this.data.file){
			this.file=this.data.file;
		}
        }
	this.store=new Ext.data.TreeStore({});
	//Get info from server
	TShark.loadPacket(
		{
			no:this.no,
			file:this.file
		},
		function(result,e){
			this.store.setRootNode(result.data); //Apply data to treestore
		},
		this
	);
	this.tree = new Ext.tree.Panel({
		store: this.store,
		border:0,
		height: '100%',
		width: '100%',
		hideHeaders: true,
		lines:true,
		rootVisible:false
	});
	this.w=new Ext.Window({
		title:this.title,
		height:500,
		width:1100,
		layout:'fit',
		modal:false,
		items:[
			this.tree
		]
	});
	this.w.show();
}