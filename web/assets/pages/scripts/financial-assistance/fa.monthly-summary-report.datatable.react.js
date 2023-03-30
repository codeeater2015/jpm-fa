var FinancialAssistanceMonthlySummaryReportDatatable = React.createClass({

    getInitialState: function () {
        return {
            target: null,
            typingTimer: null,
            doneTypingInterval: 1500,
            showReleasedListModal: false,
            user: null,
            filters: {
                electId: null,
                provinceCode: null,
                proId: null
            },
            overview : {
                data : {
                    total_granted_amt : 0,
                    total_dswd_medical : 0,
                    total_dswd_opd : 0,
                    total_doh_maip_medical : 0,
                    total_doh_maip_opd : 0,
                    total_beneficiary : 0
                }
            }
        }
    },

    componentDidMount: function () {
        this.loadUser(window.userId);
        this.initDatatable();
        this.loadOverview();
    },

    componentDidUpdate:function(){
        this.reload();
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
    
    loadOverview: function () {
        var self = this;
        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_financial_assistance_daily_summary_breakdown", { startDate: self.props.startDate, endDate : self.props.endDate }),
            type: "GET"
        }).done(function (res) {
            console.log('overview');
            console.log(res);
            self.setState({ overview: res });
        });
    },

    initDatatable: function () {
        var self = this;
        var grid = new Datatable();

        var financial_assistance_monthly_summary = $("#financial_assistance_monthly_summary");
        var grid_project_event = new Datatable();
        var url = Routing.generate("ajax_get_datatable_financial_assistance_monthly_summary_report", {}, true);

        grid_project_event.init({
            src: financial_assistance_monthly_summary,
            loadingMessage: 'Loading...',
            "dataTable": {
                "bState": true,
                "autoWidth": true,
                "deferRender": true,
                "pageLength": 100,
                buttons: [
                    'copy',
                    {
                        extend: 'excel',
                        title: function () {
                            var printTitle = 'MONTHLY SUMMARY REPORT';
                            return printTitle
                        }
                    },
                    {
                        extend: 'print',
                        title: function () {
                            var printTitle = 'MONTHLY SUMMARY REPORT';
                            return printTitle
                        }
                    }
                ],
                dom: 'Brtipl',
                "ajax": {
                    "url": url,
                    "type": 'GET',
                    "data": function (d) {
                        d.startDate = self.props.startDate;
                        d.endDate = self.props.endDate;
                    }
                },
                "columnDefs": [{
                    'orderable': false,
                    'targets': [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
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
                        "data": "month_name",
                        "width": 50,
                        "className": "text-center",
                        "render": function (data) {
                            return "<a href='javascript:void(0);' class='release-button'><strong>" + data + '</strong></a>';
                        }
                    },
                    {
                        "data": "total_doh_maip_opd",
                        "width": 50,
                        "className": "text-center",
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_doh_maip_medical",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_doh_maip_medical",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data,type,row){
                            return self.numberWithCommas(parseInt(row.total_doh_maip_opd) + parseInt(row.total_doh_maip_medical));
                        }
                    },
                    {
                        "data": "total_dswd_opd",
                        "className": "text-center",
                        "width": 50,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_dswd_medical",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_doh_maip_medical",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data,type,row){
                            return self.numberWithCommas(parseInt(row.total_dswd_opd) + parseInt(row.total_dswd_medical));
                        }
                    },
                    {
                        "data": "total_beneficiary",
                        "className": "text-center",
                        "width": 100,
                        "render": function (data, type, row) {
                            return self.numberWithCommas(parseInt(row.total_dswd_opd) + parseInt(row.total_dswd_medical) + parseInt(row.total_doh_maip_opd) + parseInt(row.total_doh_maip_medical));
                        }
                    },
                    {
                        "data": "total_beneficiary",
                        "className": "text-center",
                        "width": 100,
                        "render" : function(data){
                            return self.numberWithCommas(data);
                        }
                    },
                    {
                        "data": "total_granted_amt",
                        "className": "text-center",
                        "width": 50,
                        "render": function (data) {
                            return "<a href='javascript:void(0);' class='release-button'><strong>" + self.numberWithCommas(parseFloat(data).toFixed(2)) + '</strong></a>';
                        }
                    }
                ],
            }
        });


        financial_assistance_monthly_summary.on('click', '.release-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.setState({ showReleasedListModal: true, target: data.month_name });
        });

        financial_assistance_monthly_summary.on('click', '.delete-button', function () {
            var data = grid_project_event.getDataTable().row($(this).parents('tr')).data();
            self.delete(data.id);
        });

        self.grid = grid_project_event;
    },

    delete: function (id) {
        var self = this;

        if (confirm("Are you sure you want to delete this summary?")) {
            self.requestDelete = $.ajax({
                url: Routing.generate("ajax_delete_financial_assistance_monthly_summary", { id: id }),
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

    closeReleasedListModal: function () {
        this.setState({ showReleasedListModal: false });
    },

    numberWithCommas: function (x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    },

    render: function () {
        return (
            <div>

                {
                    this.state.showReleasedListModal &&
                    <FinancialAssistanceMonthlyListModal
                        proId={3}
                        show={this.state.showReleasedListModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        month={this.state.target}
                        startDate={this.props.startDate}
                        endDate={this.props.endDate}
                        onHide={this.closeReleasedListModal}
                    />
                }
                <div className="row">
                    <div className="col-md-12 text-right">
                        <h3> Overall Amount Released : {this.numberWithCommas(self.parseFloat(this.state.overview.data.total_granted_amt).toFixed(2))}</h3>
                    </div>
                </div> 
                <div className="table-container" style={{ marginTop: "60px" }}>
                    <table id="financial_assistance_monthly_summary" className="table table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th rowSpan="2" className="text-center">No</th>
                                <th rowSpan="2" className="text-center">Month</th>
                                <th colSpan="3" className="text-center">DOH transactions</th>
                                <th colSpan="3" className="text-center">DSWD transactions</th>
                                <th rowSpan="2" className="text-center">TOTAL transactions</th>
                                <th rowSpan="2" className="text-center">TOTAL NO. OF BENEFICIARIES</th>
                                <th rowSpan="2" className="text-center">TOTAL AMOUNT RELEASED</th>
                            </tr>
                            <tr>
                                <th className="text-center">OPD</th>
                                <th className="text-center">MEDICAL</th>
                                <th className="text-center">TOTAL</th>
                                <th className="text-center">OPD</th>
                                <th className="text-center">MEDICAL</th>
                                <th className="text-center">TOTAL</th>
                            </tr>
                            <tr >
                                <th className="text-center" colSpan="2">OVERALL TOTAL</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_doh_maip_opd))}</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_doh_maip_medical))}</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_doh_maip_opd) +  self.parseFloat(this.state.overview.data.total_doh_maip_medical))}</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_dswd_opd))}</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_dswd_medical))}</th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_dswd_opd) +  self.parseFloat(this.state.overview.data.total_dswd_medical))}</th>
                                <th className="text-center">
                                    {this.numberWithCommas(self.parseFloat(
                                        this.state.overview.data.total_dswd_opd)
                                        + self.parseFloat(this.state.overview.data.total_dswd_medical)
                                        + self.parseFloat(this.state.overview.data.total_doh_maip_opd)
                                        + self.parseFloat(this.state.overview.data.total_doh_maip_medical)
                                    )}
                                </th>
                                <th className="text-center">{this.numberWithCommas(self.parseFloat(this.state.overview.data.total_beneficiary))}</th>
                                <th className="text-center"> {this.numberWithCommas(self.parseFloat(this.state.overview.data.total_granted_amt).toFixed(2))}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
});

window.FinancialAssistanceMonthlySummaryReportDatatable = FinancialAssistanceMonthlySummaryReportDatatable;