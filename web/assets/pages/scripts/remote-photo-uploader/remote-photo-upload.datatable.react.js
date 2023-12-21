var RemotePhotoUploadDatatable = React.createClass({

    getInitialState: function () {
        return {
            showPhotoModal: false,
            showItemsModal : false,
            targetId: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            gPhotos : 0,
            gLinked : 0,
            gId : 0,
            gUploads : 0,
            gTarget : 0,
            gUnlinked : 0
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.initDatatable);
        });
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();
        var field_photo_table = $("#field_photo_table");

        grid.init({
            src: field_photo_table,
            dataTable: {
                "bState": true,
                "autoWidth": true,
                "serverSide": true,
                "processing": true,
                "deferLoading" : true,
                "ajax": {
                    "url": Routing.generate('ajax_datatable_remote_upload'),
                    "type": "GET",
                    "data": function (d) {
                        d.barangayName = $('#field_photo_table input[name="barangay_name"]').val();
                        d.voterGroup = self.props.voterGroup;
                        d.uploadDate = $('#field_photo_table input[name="upload_date"]').val();
                        d.municipalityName = self.props.municipalityName;

                        self.setState({
                            gPhotos : 0,
                            gLinked : 0,
                            gUnlinked : 0,
                            gId : 0,
                            gUploads : 0,
                            gTarget : 0
                        }); 
                    }
                },
                pageLength: 100,
                columnDefs: [
                    {
                        'className': 'text-center valign-middle',
                        'orderable': false,
                        'targets': [0, 2, 3, 4, 5]
                    }
                ],
                "order": [
                    [1, "asc"]
                ],
                "columns": [
                    {
                        "data": null,
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, full, meta) {
                            return meta.settings._iDisplayStart + meta.row + 1;
                        }
                    },
                    {
                        "data": "barangay_name",
                        "className" : "text-center",
                        "render" : function(data,type,row){
                            return '<strong class="font-blue">' +  data +'</strong>' ;
                        }
                    },
                    {
                        "data": "total_photos",
                        "className": "text-center",
                        "width": 40,
                        "render" : function(data,type,row){

                            var totalMember = 0;
                            var totalPrecints = parseInt(row.total_precincts);

                            switch(self.props.voterGroup){
                                case 'LPPP' : 
                                    totalMember = totalPrecints;
                                    break;
                                case 'LPPP1' :
                                    totalMember = totalPrecints * 6;
                                    break;
                                case 'LPPP2' : 
                                    totalMember = totalPrecints * 6 * 4;
                                    break;
                                case 'LPPP3' :
                                    totalMember = totalPrecints * 6 * 4 * 4;
                                    break;
                            }

                            var fontClass = "";
                            if(parseInt(data) > totalMember)
                                fontClass = 'font-red';
                            else if(parseInt(data) < totalMember)
                                fontClass = 'font-blue';
                            else
                                fontClass = '';

                            return parseInt(data) == 0 ? "" : '<strong class="' + fontClass + '">' +  data +'</strong>' ;
                        }
                    },
                    {
                        "data": "total_cleared",
                        "className": "text-center",
                        "width": 40
                    },
                    {
                        "data": "total_photos",
                        "className": "text-center",
                        "width": 40,
                        "render" : function(data, type, row){
                            return parseInt(data) - parseInt(row.total_cleared);
                        }
                    },
                   
                    {
                        "width": 100,
                        "render": function (data, type, row) {
                            var editBtn = '<button class="btn btn-xs green edit-btn"><i class="fa fa-edit"></i></button>';
                            var deleteBtn = '<button class="btn btn-xs red-sunglo delete-btn"><i class="fa fa-trash"></i></button>';
                            var itemsBtn = '<button class="btn btn-xs blue items-btn"><i class="fa fa-file"></i></button>';
                            var downloadBtn = '<button class="btn btn-xs green download-btn"><i class="fa fa-download"></i></button>';

                            var btnGroup = '';
                            btnGroup += editBtn;
                            //btnGroup += itemsBtn;
                            
                            if(self.state.user != null && self.state.user.isAdmin){
                                btnGroup += downloadBtn;
                                btnGroup += deleteBtn;
                            }

                            return row.id != null && row.voter_group == self.props.voterGroup ? btnGroup : "";
                        },
                        "className": "text-center"
                    }
                ]
            }

        });


        field_photo_table.on('click', '.edit-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showPhotoModal: true, targetId: data.id });
        });

        
        field_photo_table.on('click', '.items-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showItemsModal: true, targetId: data.id });
        });

        field_photo_table.on('click', '.delete-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        field_photo_table.on('click', '.download-btn', function () {
            var data = grid.getDataTable().row($(this).parents('tr')).data();
            var url = Routing.generate("ajax_get_download_photo_album", { id: data.id });

            window.location.assign(url);
        });

        self.grid = grid;
    },

    delete: function (id) {
        var self = this;

        if (confirm("are you sure you want to clear image files?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_remote_photo_upload", { id: id }),
                type: "DELETE"
            }).done(function (res) {
                self.reload();
            });
        }
    },

    handleFilterChange: function () {
        var self = this;
        clearTimeout(this.state.typingTimer);
        this.state.typingTimer = setTimeout(function () {
            self.reload();
        }, this.state.doneTypingInterval);
    },
    
    closeItemsModal:function(){
        this.setState({ showItemsModal : false, targetId : null});
    },

    reload: function () {
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    closePhotoModal: function () {
        this.setState({ showPhotoModal: false, targetId: null })
    },

    render: function () {
        return (
            <div>
                {
                    this.state.showPhotoModal &&
                    <RemotePhotoUploadModal
                        show={this.state.showPhotoModal}
                        onHide={this.closePhotoModal}
                        id={this.state.targetId}
                    />
                }

                {
                    this.state.showItemsModal &&
                    <RemotePhotoUploadItemsModal
                        show={this.state.showItemsModal}
                        onHide={this.closeItemsModal}
                        id={this.state.targetId}
                    />
                }

                <div className="table-container">
                    <div className="table-actions-wrapper">
                    </div>
                    <table id="field_photo_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th className="text-center">No</th>
                                <th className="text-center">Barangay</th>
                                <th className="text-center">Uploads</th>
                                <th className="text-center">Downloaded</th>
                                <th className="text-center">Pending Downloads</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }} className="text-right">Totals</td>
                                <td className="text-center"> {this.state.gUploads}</td>
                                <td className="text-center">{this.state.gLinked}</td>
                                <td className="text-center">{this.state.gLinked}</td>
                                <td className="text-center">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="barangay_name" onChange={this.handleFilterChange} />
                                </td>
                                <td ></td>
                                <td ></td>
                                <td ></td>
                                <td className="text-center">
                                    <button style={{ marginTop: "5px", marginBottom: "5px" }} className="btn btn-xs green btn-outline filter-submit">
                                        <i className="fa fa-search" />Search
                                    </button>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.RemotePhotoUploadDatatable = RemotePhotoUploadDatatable;