var KforceDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showAttendanceModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        this.initDatatable();
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res });
        });
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var kforce_list_table = $("#kforce_list_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_kforce", {}, true);

        grid_project_event.init({
            src: kforce_list_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.electId = $('#voter_component #election_select2').val();
                        d.provinceCode = $('#voter_component #province_select2').val();
                        d.proId = $('#voter_component #project_select2').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3]
                }],
                "order": [
                    [0, "desc"]
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
                    { "data": "event_name" },
                    {
                        "data": "event_date",
                        "width": 100,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            return moment(data).format('MMM DD, YYYY');
                        }
                    },
                    {
                        "data": "event_date",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return "";
                        }
                    },
                    {
                        "data": "event_date",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return "";
                        }
                    },
                    {
                        "data": "event_date",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return "";
                        }
                    },
                    {
                        "data": "event_date",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return "";
                        }
                    },
                    {
                        "data": "event_date",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return "";
                        }
                    },
                    {
                        "width" : 70,
                        "className" : "text-center",
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green-dark edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit' ></i></a>";
                            var attendanceBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-green attendance-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-calendar'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return editBtn + attendanceBtn + deleteBtn;
                        }
                    }
                ],
            }
        });

        kforce_list_table.on('click', '.edit-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.event_id });
        });

        kforce_list_table.on('click', '.attendance-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showAttendanceModal: true, target: data.event_id });
        });

        kforce_list_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.event_id);
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },

    closeAttendanceModal: function () {
        this.setState({ showAttendanceModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    delete: function (eventId) {
        var self = this;

        if (confirm("Are you sure you want to delete this event?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_kforce_header", { eventId: eventId }),
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

    reload: function () {
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div className="bg-grey" style={{ padding : "20px" , borderRadius : "10px"}}>
                {
                    this.state.showCreateModal &&
                    <KforceCreateModal
                        show={this.state.showCreateModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeCreateModal}
                    />
                }

                {
                    this.state.showEditModal &&
                    <KforceEditModal
                        eventId={this.state.target}
                        show={this.state.showEditModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                    />
                }

                {
                    this.state.showAttendanceModal &&
                    <KforceDetailModal
                        eventId={this.state.target}
                        show={this.state.showAttendanceModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeAttendanceModal}
                    />
                }

                <div className="row ">
                    <div className="col-md-5">
                        <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Kforce List</button>
                    </div>
                </div>

                <div className="row">
                    <div className="col-md-12">
                        <div className="table-container" style={{ marginTop: "20px" , backgroundColor: "white", padding : "20px", borderRadius : "20px"}}>
                            <table id="kforce_list_table" className="table table-condensed table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th className="text-center">No</th>
                                        <th className="text-center">List Description</th>
                                        <th className="text-center">Date </th>
                                        <th className="text-center">Voter</th>
                                        <th className="text-center">Non Voter</th>
                                        <th className="text-center">KFC & KFORCE</th>
                                        <th className="text-center">KFORCE ONLY</th>
                                        <th className="text-center">TOTAL</th>
                                        <th width="90px"></th>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style={{ padding: "10px 5px" }}>
                                            <input type="text" className="form-control form-filter input-sm" name="idx_no" onChange={this.handleFilterChange} />
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
});

window.KforceDatatable = KforceDatatable;