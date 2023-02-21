var TupadSummaryComponent = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            municipalityName: null
        }
    },

    componentDidMount: function () {
        this.initSelect2();
    },


    initSelect2: function () {
        var self = this;

        $("#tupad_summary_component #municipality_select2").select2({
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
                            return { id: item.name, text: item.name };
                        })
                    };
                },
            }
        });

        $("#tupad_summary_component #municipality_select2").on("change", function () {
            self.setState({ municipalityName: $(this).val() });
        });
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    render: function () {
        var self = this;

        console.log('ttest');
        console.log(this.state.municipalityName);

        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body" id="tupad_summary_component">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Assistance Summary</h2>
                        </div>
                        <br />
                        <div className="col-md-2">
                            <label>Breakdown by Municipality : </label>
                            <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityName">
                            </select>
                        </div>
                    </div>
                    <br />
                    <br />
                    <div className="row">
                        {this.state.municipalityName != null ? <TupadMunicipalitySummary municipalityName={this.state.municipalityName} /> : <TupadProvinceSummary />}
                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <TupadSummaryComponent />,
        document.getElementById('page-container')
    );
}, 500);
