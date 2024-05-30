var KfcAttendanceDetailDatatable = React.createClass({

    getInitialState: function () {
        return {
            showEditModal: false,
            showReleaseModal: false,
            showDetailModal : false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showDetailModal: false,
            user: null,
            filters: {
                electId: null,
                provinceCode: null,
                proId: null
            }
        }
    },

    componentDidMount: function () {
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

        var kfc_attendance_detail_table = $("#kfc_attendance_detail_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_kfc_attendance_detail", {}, true);

        grid_project_event.init({
            src: kfc_attendance_detail_table,
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
                        d.hdrId = self.props.id
                        d.voterName = $('#kfc_attendance_detail_table input[name="voter_name"]').val();
                        d.municipalityName = $('#kfc_attendance_detail_table input[name="municipality_name"]').val();
                        d.barangayName = $('#kfc_attendance_detail_table input[name="barangay_name"]').val();
                        d.contactNo = $('#kfc_attendance_detail_table input[name="contact_no"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7, 8, 9]
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
                    {
                        "data": "voter_name",
                        "className": "text-left"
                    },
                    {
                        "data": "municipality_name",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "barangay_name",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "contact_no",
                        "className": "text-center",
                        "width": 100,
                    },
                    {
                        "data": "has_profile",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="hasProfile" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "total_profile",
                        "className": "text-center",
                        "width": 30
                    },
                    {
                        "data": "has_assignment",
                        "className": "text-center",
                        "width": 30,
                        "render": function (data, type, row) {
                            return '<label class="mt-checkbox status-checkbox"><input type="checkbox" name="hasAssignment" ' + ((parseInt(data) == 1) ? ' checked="checked" ' : '') + ' value="' + row.id + '"></input><span></span></label>';
                        }
                    },
                    {
                        "data": "total_assignment",
                        "className": "text-center",
                        "width": 30
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

        kfc_attendance_detail_table.on('click', '.edit-btn', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({showDetailModal : true, target : data.id });
        });

        kfc_attendance_detail_table.on('click', '.delete-btn', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        kfc_attendance_detail_table.on('click', '.status-checkbox', function (e) {
            var id = e.target.value;
            var checked = e.target.checked;
            var fieldName = e.target.name;
            var newValue = checked ? 1 : 0;

            if (id != null && checked != null) {
                self.patchStatus(id, fieldName, newValue);
            }
        });

        self.grid = grid_project_event;
    },

    patchStatus: function (id, fieldName, value) {
        var self = this;
        var data = {};

        data[fieldName] = value;
        self.requestToggleRequirement = $.ajax({
            url: Routing.generate("ajax_patch_attendance_tag_has_profile", { id: id }),
            type: "PATCH",
            data: (data)
        }).done(function (res) {
            self.reload();
        });
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

    closeDetailModal: function () {
        this.setState({ showDetailModal: false , target : null});
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this request?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_kfc_attendance_detail", { id: id }),
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

                <div className="table-container" style={{ marginTop: "20px" }}>

                    {
                        this.state.showDetailModal &&
                        <KfcAttendanceDetailModal
                            show={this.state.showDetailModal}
                            onHide={this.closeDetailModal}
                            hdrId={this.state.target}
                            reloadDetail={this.reload}
                        />
                    }

                    <table id="kfc_attendance_detail_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Municipality</th>
                                <th>Barangay</th>
                                <th>Contact No</th>
                                <th>Profile</th>
                                <th>Total</th>
                                <th>Assignment</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                </td>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="municipality_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="barangay_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="contact_no" onChange={this.handleFilterChange} />
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

window.KfcAttendanceDetailDatatable = KfcAttendanceDetailDatatable;