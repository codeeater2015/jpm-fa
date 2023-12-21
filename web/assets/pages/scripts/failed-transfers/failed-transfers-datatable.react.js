var FailedTransferDatatable = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showRecruitsModal: false,
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            user: null,
            filters: {
                electId: 4,
                provinceCode: 53,
                proId: 3
            }
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        this.initSelect2();
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

    initSelect2: function () {
        var self = this;

        $("#handler_component #election_select2").select2({
            casesentitive: false,
            placeholder: "Select Election...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_elections'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.elect_id, text: item.elect_name };
                        })
                    };
                },
            }
        });

        $("#handler_component #project_select2").select2({
            casesentitive: false,
            placeholder: "Select Project...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_projects'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.pro_id, text: item.pro_name };
                        })
                    };
                },
            }
        });

        $("#handler_component #province_select2").select2({
            casesentitive: false,
            placeholder: "Enter Province...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_province_strict'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.province_code, text: item.name };
                        })
                    };
                },
            }
        });

        $("#handler_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#handler_component #province_select2").on("change", function () {
            var filters = self.state.filters;
            filters.provinceCode = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

    },

    reinitSelect2: function () {
        var self = this;

        if (!self.isEmpty(self.state.user.project)) {
            var provinceCode = self.state.user.project.provinceCode;

            self.requestProvince = $.ajax({
                url: Routing.generate("ajax_get_province", { provinceCode: provinceCode }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#handler_component #project_select2").empty()
                    .append($("<option/>")
                        .val(res.proId)
                        .text(res.proName))
                    .trigger("change");

                self.initDatatable();
            });
        }

        self.requestActiveElection = $.ajax({
            url: Routing.generate("ajax_get_active_election"),
            type: "GET"
        }).done(function (res) {
            $("#handler_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#handler_component #election_select2").attr('disabled', 'disabled');
            $("#handler_component #province_select2").attr('disabled', 'disabled');
            $("#handler_component #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var member_status = $("#member_status");
        var grid_member_status = new Datatable();
        var url = Routing.generate("ajax_get_datatable_failed_transfers", {}, true);

        console.log("initializing datatable");

        grid_member_status.init({
            src: member_status,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.voterName = $('#member_status input[name="voter_name"]').val();
                        d.voterGroup = $('#member_status input[name="voter_group"]').val();
                        d.municipalityName = $('#member_status input[name="municipality_name"]').val();
                        d.barangayName = $('#member_status input[name="barangay_name"]').val();
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 2, 3, 4, 5, 6, 7]
                }, {
                    'className': 'align-center',
                    'targets': [2, 3]
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
                    { "data": "municipality_name", "className": "text-center", width: 150 },
                    { "data": "barangay_name", width: 220 },
                    { 
                        "data": "voter_name",
                        "className" : "text-left"
                    },
                    { 
                        "data": "voter_group" ,
                        "width" : 80,
                        "className" : "text-center"  
                    },
                    {
                        "data": "has_photo",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return data == 1 ? "YES" : "NO"
                        }
                    },
                    {
                        "data": "has_id",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return data == 1 ? "YES" : "NO"
                        }
                    },
                    {
                        "data": "is_non_voter",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return data == 1 ? "YES" : "NO"
                        }
                    },
                    {
                        "width": 90,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            var editBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-primary edit-button' data-toggle='tooltip' data-title='Edit'><i class='fa fa-edit'></i></a>";
                            var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                            return editBtn + deleteBtn;
                        }
                    }
                ],
            }
        });

        member_status.on('click', '.edit-button', function () {
            var data = grid_member_status.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        })

        member_status.on('click', '.delete-button', function () {
            var data = grid_member_status.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_member_status;
    },

    openTransferModal : function () {
        this.setState({ showTransferModal : true });
    },

    closeTransferModal: function () {
        this.setState({ showTransferModal: false});
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this record ?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_form_status", { id: id }),
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
            <div>

                {
                    this.state.showTransferModal &&
                    <FailedTransferTroubleshootModal
                        show={this.state.showTransferModal}
                        reload={this.reload}
                        onHide={this.closeTransferModal}
                    />
                }

                <div className="row" id="handler_component">
                    <div className="col-md-5">
                        <button type="button" className="btn btn-primary" onClick={this.openTransferModal}>Troubleshoot Voter</button>
                    </div>

                    <div className="col-md-7">
                        <form onSubmit={this.onApplyCode}>
                            <div className="col-md-3 col-md-offset-1">
                                <select id="election_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-4">
                                <select id="province_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                            <div className="col-md-4">
                                <select id="project_select2" className="form-control form-filter input-sm" >
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="member_status" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th className="text-center">Municipality</th>
                                <th className="text-center">Barangay</th>
                                <th className="text-center">Voter Name</th>
                                <th className="text-center">Voter Group</th>
                                <th className="text-center">Photo</th>
                                <th className="text-center">Id</th>
                                <th className="text-center">Non-voter</th>
                                <th width="60px" className="text-center"></th>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="municipality_name" onChange={this.handleFilterChange} />
                                </td>
                                <td>
                                    <input type="text" className="form-control form-filter input-sm" name="barangay_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="voter_group" onChange={this.handleFilterChange} />
                                </td>
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

window.FailedTransferDatatable = FailedTransferDatatable;