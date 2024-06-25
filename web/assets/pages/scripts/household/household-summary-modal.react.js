var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HouseholdSummaryModal = React.createClass({
    getInitialState: function () {
        return {
            summary: [],
        }
    },

    componentDidMount: function () {
        this.loadSummary();
    },

    printPage: function () {
        console.log('printing');
        $.print("#household_summary1_table" /*, options*/);
    },

    loadSummary: function (userId) {
        var self = this;

        console.log('municipality no');
        console.log(this.props.municipalityNo);
        self.requestUser = $.ajax({
            url: Routing.generate("ajax_m_get_household_voters_summary_by_barangay", { municipalityNo: this.props.municipalityNo }),
            type: "GET"
        }).done(function (res) {
            console.log("summary has been received");
            self.setState({ summary: res });
        });
    },

    render: function () {
        var self = this;
        var data = self.state.header;
        var gPuerto = 0;
        var gAborlan = 0;
        var gHousehold = 0;
        var gOutside = 0;
        var gPotential = 0;
        var gTotal = 0;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Municipality Breakdown Summary</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    <div className="row">
                        <div className="col-md-12 text-right">
                            <button type="button" className="btn btn-primary" onClick={this.printPage}>Print Page</button>
                        </div>
                    </div>
                    <br/>
                    <div>
                        <table id="household_summary1_table" className="table table-condensed table-bordered">
                            <thead style={{ backgroundColor: "#5ab866" }}>
                                <tr className="text-center">
                                    <th rowSpan="2" className="text-center">Household Address</th>
                                    <th rowSpan="2" className="text-center">Households</th>
                                    <th rowSpan="2" className="text-center">Est. Target</th>
                                    <th colSpan="2" className="text-center">Voting Address</th>
                                    <th rowSpan="2" className="text-center">Outside</th>
                                    <th rowSpan="2" className="text-center">Potential</th>
                                    <th rowSpan="2" className="text-center">Total</th>
                                </tr>
                                <tr>
                                    <th className="text-center">Puerto</th>
                                    <th className="text-center">Aborlan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {
                                    this.state.summary.map(function (item, index) {

                                        gTotal += Number.parseInt(item.total_household);
                                        gPuerto += Number.parseInt(item.total_puerto);
                                        gHousehold += Number.parseInt(item.total_household);
                                        gAborlan += Number.parseInt(item.total_aborlan);
                                        gOutside += Number.parseInt(item.total_outside);
                                        gPotential += Number.parseInt(item.total_potential);

                                        return (
                                            <tr>
                                                <td className="text-left">{++index}. {item.asn_barangay_name}</td>
                                                <td className="text-center">{item.total_household}</td>
                                                <td className="text-center">{Number.parseInt(item.total_household) * 4}</td>
                                                <td className="text-center">{Number.parseInt(item.total_puerto)}</td>
                                                <td className="text-center">{Number.parseInt(item.total_aborlan)}</td>
                                                <td className="text-center">{Number.parseInt(item.total_outside)}</td>
                                                <td className="text-center">{Number.parseInt(item.total_potential)}</td>
                                                <td className="text-center"><strong>{Number.parseInt(item.total_puerto) + Number.parseInt(item.total_aborlan) + Number.parseInt(item.total_potential)}</strong></td>
                                            </tr>
                                        );
                                    })
                                }
                                <tr>
                                    <td className="text-center"><strong>Total</strong></td>
                                    <td className="text-center"><strong>{gHousehold}</strong></td>
                                    <td className="text-center"><strong>{gHousehold * 4}</strong></td>
                                    <td className="text-center"><strong>{gPuerto}</strong></td>
                                    <td className="text-center"><strong>{gAborlan}</strong></td>
                                    <td className="text-center"><strong>{gOutside}</strong></td>
                                    <td className="text-center"><strong>{gPotential}</strong></td>
                                    <td className="text-center"><strong>{gPuerto + gAborlan + gPotential}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Modal.Body>
            </Modal>
        );
    }

});


window.HouseholdSummaryModal = HouseholdSummaryModal;