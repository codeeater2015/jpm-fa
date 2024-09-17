var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var SummaryV1Component = React.createClass({

    getInitialState: function () {
        return {
            municipalityNo: "",
            summaryDate: ""
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#summary_v1_table #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#summary_v1_table #date_select2").select2({
            casesentitive: false,
            placeholder: "Select...",
            allowClear: true,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_summary_date'),
                data: function (params) {
                    return {
                        searchText: params.term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.summary_date, text: item.summary_date };
                        })
                    };
                },
            }
        });



        $("#summary_v1_table #municipality_select2").on("change", function () {
            self.setState({ municipalityNo: $(this).val() })
        });

        $("#summary_v1_table #date_select2").on("change", function () {
            self.setState({ summaryDate: $(this).val() })
        });
    },


    render: function () {
        let municipalityNo = this.state.municipalityNo;
        let summaryDate = this.state.summaryDate;

        console.log(municipalityNo);
        console.log(summaryDate);

        return (
            <div id="summary_v1_table" className="portlet light portlet-fit bordered bg-grey">
                <div className="portlet-body ">
                    <h2>Organization Summary</h2>
                    <br />

                    <div className="row">
                        <div className="col-md-3">
                            <div className="form-group">
                                <label className="control-label">City/Municipality</label>
                                <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                </select>
                            </div>
                        </div>

                        <div className="col-md-3">
                            <FormGroup controlId="formBarangay" >
                                <label className="control-label">Barangay</label>
                                <select id="date_select2" className="form-control form-filter input-sm" name="brgyNo">
                                </select>
                            </FormGroup>
                        </div>
                    </div>

                    {(this.state.municipalityNo == '16' && this.state.summaryDate != "" && this.state.summaryDate != null) ? (
                        <div>
                            <h3><strong>Overall Total</strong></h3>
                            <SummaryV1AllTable municipalityNo={16} displayDetail={false} summaryDate={this.state.summaryDate} />
                            <h3><strong>North Cluster</strong></h3>
                            <SummaryV1Table clusterName="C3" municipalityNo={16} summaryDate={this.state.summaryDate} />
                            <h3><strong>North West Cluster</strong></h3>
                            <SummaryV1Table clusterName="C4" municipalityNo={16} summaryDate={this.state.summaryDate} />
                            <h3><strong>Poblacion Cluster</strong></h3>
                            <SummaryV1Table clusterName="C1" municipalityNo={16} summaryDate={this.state.summaryDate} />
                            <h3><strong>South Cluster</strong></h3>
                            <SummaryV1Table clusterName="C5" municipalityNo={16} summaryDate={this.state.summaryDate} />
                            <h3><strong>WestCoast Cluster</strong></h3>
                            <SummaryV1Table clusterName="C6" municipalityNo={16} summaryDate={this.state.summaryDate} />
                        </div>
                    )
                        : ""

                    }

                    {(this.state.municipalityNo == '01' && this.state.summaryDate != "" && this.state.summaryDate != null) ? (
                        <div>
                            <SummaryV1AllTable municipalityNo={'01'} displayDetail={true} summaryDate={this.state.summaryDate} />
                        </div>
                    )
                        : ""
                    }
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <SummaryV1Component />,
        document.getElementById('page-container')
    );
}, 500);
