var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaMemberModal = React.createClass({
    getInitialState: function () {
        return {
            member: null,
            showAddMemberModal: false,
            header: {
                householdCode: "",
                voterName: "",
                barangayName: "",
                municipalityName: "",
                cellphone: ""

            }
        }
    },

    render: function () {
        var self = this;
        var data = self.state.header;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>{data.voterName} </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <div className="col-md-12" style={{ paddingLeft: "0px", marginBottom: "10px" }}>
                        <button onClick={this.openAddMemberModal} type="button" className="btn btn-sm btn-primary">Add Member</button>
                    </div>
                    {
                        this.state.showAddMemberModal &&
                        <KamadaMemberCreateModal
                            proId={self.props.proId}
                            provinceCode={53}
                            municipalityNo={this.state.header.municipalityNo}
                            municipalityName={this.state.header.municipalityName}
                            barangayNo={this.state.header.barangayNo}
                            barangayName={this.state.header.barangayName}

                            electId={self.props.electId}
                            hdrId={this.props.id}
                            show={this.state.showAddMemberModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAddMemberModal}
                        />
                    }
                    <KamadaDetailDatatable ref="DetailDatatable" hdrId={this.props.id} />
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.loadHeader(this.props.id);
    },

    loadHeader: function (id) {
        var self = this;

        self.requestRecruiter = $.ajax({
            url: Routing.generate("ajax_get_kamada_header", { id: id }),
            type: "GET"
        }).done(function (res) {
            self.setState({ header: res });
        });
    },

    setFormProp: function (e) {
        let header = this.state.header;
        header[e.target.name] = e.target.value;

        this.setState({ header: header });
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    openAddMemberModal: function () {
        console.log("showing add member modal");
        this.setState({ showAddMemberModal: true })
    },

    closeAddMemberModal: function () {
        this.setState({ showAddMemberModal: false });
    }
});


window.KamadaMemberModal = KamadaMemberModal;