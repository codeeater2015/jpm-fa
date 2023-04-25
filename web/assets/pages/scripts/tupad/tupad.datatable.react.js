var TupadDatatable = React.createClass({

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
        this.loadUser(window.userId);
        //this.initSelect2();
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

        $("#fa_component #election_select2").select2({
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

        $("#fa_component #project_select2").select2({
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

        $("#fa_component #province_select2").select2({
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

        $("#fa_component #election_select2").on("change", function () {
            var filters = self.state.filters;
            filters.electId = $(this).val();

            self.setState({ filters: filters }, self.reload);
        });

        $("#fa_component #project_select2").on("change", function () {
            var filters = self.state.filters;
            filters.proId = $(this).val();
            self.setState({ filters: filters }, self.reload);
        });

        $("#fa_component #province_select2").on("change", function () {
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
                $("#fa_component #province_select2").empty()
                    .append($("<option/>")
                        .val(res.province_code)
                        .text(res.name))
                    .trigger("change");
            });

            self.requestProject = $.ajax({
                url: Routing.generate("ajax_get_project", { proId: self.state.user.project.proId }),
                type: "GET"
            }).done(function (res) {
                $("#fa_component #project_select2").empty()
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
            $("#fa_component #election_select2").empty()
                .append($("<option/>")
                    .val(res.electId)
                    .text(res.electName))
                .trigger("change");
        });

        if (!self.state.user.isAdmin) {
            $("#fa_component #election_select2").attr('disabled', 'disabled');
            $("#fa_component #province_select2").attr('disabled', 'disabled');
            $("#fa_component #project_select2").attr('disabled', 'disabled');
        }
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var tupad_table = $("#tupad_table");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_tupad_transactions", {}, true);

        grid_project_event.init({
            src: tupad_table,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.sourceMunicipality = self.props.sourceMunicipality;
                        d.sourceBarangay = self.props.sourceBarangay;
                        d.serviceType = self.props.serviceType;
                        d.source = self.props.source;
                        d.releaseDate = self.props.releaseDate;

                        d.bName = $('#tupad_table input[name="beneficiary_name"]').val();
                        d.bMunicipality = $('#tupad_table input[name="b_municipality"]').val();
                        d.bBarangay = $('#tupad_table input[name="b_barangay"]').val();

                        console.log("active source", self.props.source);
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
                        "data": "b_name",
                        "className": "text-left",
                        "render": function (data) {
                            return "<a href='javascript:void(0);' class='release-button'><strong>" + data + '</strong></a>';
                        }
                    },
                    {
                        "data": "service_type",
                        "className": "text-center",
                        "width": 150,
                    },
                    {
                        "data": "source_municipality",
                        "width": 150,
                        "className": "text-center"
                    },
                    {
                        "data": "source_barangay",
                        "className": "text-center",
                        "width": 180
                    },
                    {
                        "data": "b_municipality",
                        "width": 150,
                        "className": "text-center"
                    },
                    {
                        "data": "b_barangay",
                        "className": "text-center",
                        "width": 180
                    },
                    {
                        "data": "is_voter",
                        "className": "text-center",
                        "width": 50,
                        "render": function (data) {
                            return parseInt(data) == 1 ? "YES" : "NO";
                        }
                    },

                    {
                        "data": "source",
                        "className": "text-center",
                        "width": 50
                    },

                    {
                        "data": "release_date",
                        "className": "text-center",
                        "width": 50
                    }

                    // {
                    //     "width": 50,
                    //     "className": "text-center",
                    //     "render": function (data, type, row) {
                    //         var deleteBtn = "<a href='javascript:void(0);' class='btn btn-xs font-white bg-red-sunglo delete-button' data-toggle='tooltip' data-title='Delete'><i class='fa fa-trash' ></i></a>";
                    //         return "";
                    //     }
                    // }
                ],
            }
        });

        tupad_table.on('click', '.release-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showEditModal: true, target: data.id });
        });

        tupad_table.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false, target: null });
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
                url: Routing.generate("ajax_delete_tupad_transaction", { id: id }),
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

    isEmpty: function (value) {
        return value == null || value == "" || value == "undefined" || value <= 0;
    },

    render: function () {
        return (
            <div>

                {
                    this.state.showEditModal &&
                    <TupadEditModal
                        proId={3}
                        show={this.state.showEditModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeEditModal}
                        trnId={this.state.target}
                    />
                }
                <div className="table-container" style={{ marginTop: "20px" }}>
                    <table id="tupad_table" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>BeneficiaryName</th>
                                <th>Service</th>
                                <th>Asst. Municipality</th>
                                <th>Asst Barangay</th>
                                <th>Reg. Municipality</th>
                                <th>Reg. Barangay</th>
                                <th>Is Voter</th>
                                <th>Source</th>
                                <th>Date Released</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="beneficiary_name" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}></td>
                                <td style={{ padding: "10px 5px" }}></td>
                                <td style={{ padding: "10px 5px" }}></td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="b_municipality" onChange={this.handleFilterChange} />
                                </td>
                                <td style={{ padding: "10px 5px" }}>
                                    <input type="text" className="form-control form-filter input-sm" name="b_barangay" onChange={this.handleFilterChange} />
                                </td>
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

window.TupadDatatable = TupadDatatable;