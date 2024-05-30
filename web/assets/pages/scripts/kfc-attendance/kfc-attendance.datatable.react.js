var KfcAttendanceDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEditModal: false,
            showReleaseModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            filters: {
                electId: null,
                provinceCode: null,
                proId: null
            }
        }
    },

    componentDidMount: function () {
        //this.loadUser(window.userId);
        this.initDatatable();
    },

    loadUser: function (userId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_user", { id: userId }),
            type: "GET"
        }).done(function (res) {
            self.setState({ user: res }, self.reinitSelect2);
        });
    },


    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var kfc_attendance_table = $("#kfc_attendance_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_kfc_attendance", {}, true);

        grid_project_event.init({
            src: kfc_attendance_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "pageLength": 100,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.description = $('#kfc_attendance_table input[name="description"]').val();
                        d.municipalityName = $('#kfc_attendance_table input[name="municipality_name"]').val();
                        d.barangayName = $('#kfc_attendance_table input[name="barangay_name"]').val();
                        d.meetingDate = $('#kfc_attendance_table input[name="meeting_date"]').val();
                        d.meetingPosition = $('#kfc_attendance_table input[name="meeting_position"]').val();
                        d.meetingGroup = $('#kfc_attendance_table input[name="meeting_group"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5,6,7,8,9,10]
                }, {
                    'className': 'align-center',
                    'targets': [0, 3]
                }],
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
                        "data": "description",
                        "className": "text-left",
                    },
                    {
                        "data": "municipality_name",
                        "className": "text-center",
                        "width": 250,
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "meeting_group",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "meeting_position",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "meeting_date",
                        "className": "text-center",
                        "width": 100,
                    },
                    {
                        "data": "total_attendee",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "total_attendee_profile",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "data": "total_attendee_assignment",
                        "className": "text-center",
                        "width": 50,
                    },
                    {
                        "width": 80,
                        "render": function (data, type, row) {

                            var viewBtn = '<button class="btn btn-xs default edit2-btn"><i class="fa fa-eye"></i></button>';
                            var editBtn = '<button class="btn btn-xs green edit-btn"><i class="fa fa-edit"></i></button>';
                            var assignBtn = '<button class="btn btn-xs blue assign-btn"><i class="fa fa-edit"></i>Assign</button>';
                            var deleteBtn = '<button class="btn btn-xs red-sunglo delete-btn"><i class="fa fa-trash"></i></button>';
                            var btnGroup = '';

                            btnGroup += editBtn + deleteBtn;

                            return btnGroup;
                        },

                        "className": "text-center"
                    }
                ],
            }
        });

        kfc_attendance_table.on('click', '.edit-btn', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        });

        kfc_attendance_table.on('click', '.delete-btn', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
        this.reload();
    },

    closeReleaseModal: function () {
        this.setState({ showReleaseModal: false, target: null });
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    openClosingModal: function () {
        this.setState({ showClosingModal: true });
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this request?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_kfc_attendance", { id: id }),
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

        console.log("relaod databable");
        if (this.grid != null) {
            this.grid.getDataTable().ajax.reload();
        }
    },

    openProfile: function (id) {
        this.setState({ showEditModal: true, target: id });
    },

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>
                {
                    this.state.showEditModal &&
                    <KfcAttendanceListModal
                        show={this.state.showEditModal}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                        id={this.state.target}
                    />
                }
                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="kfc_attendance_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Description</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Group</th>
                                <th>Position</th>
                                <th>Date</th>
                                <th>Attendee</th>
                                <th>With Profiles</th>
                                <th>With Assignment</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="description" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="municipality_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="barangay_name" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                     <input type="text" className="form-control form-filter input-sm" name="meeting_group" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="meeting_position" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="meeting_date" onChange={this.handleFilterChange} />
                                </td>
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
        )
    }
});

window.KfcAttendanceDatatable = KfcAttendanceDatatable;