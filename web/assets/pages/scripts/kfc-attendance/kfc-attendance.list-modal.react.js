var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KfcAttendanceListModal = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false
        };
    },

    openCreateModal: function () {
        console.log("open sms modal");
        this.setState({ showCreateModal: true });
        console.log(this.state.showCreateModal);
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false });
    },

    reloadDatatable: function () {
        this.refs.detailDatatable.reload();
    },
    
    onCreateSuccess : function() {
        this.reloadDatatable();
    },

    render: function () {
        var self = this;
        return (
            <Modal style={{ marginTop: "10px" }} dialogClassName="modal-custom-85" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>KFC attendance</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showCreateModal &&
                        <KfcAttendanceAddAttendeeModal
                            show={this.state.showCreateModal}
                            onHide={this.closeCreateModal}
                            onSuccess={this.onCreateSuccess}
                            id={this.props.id}
                        />
                    }

                    <div className="row">
                        <div className="col-md-12">
                            <button type="button" className="btn btn-success btn-sm" style={{ marginRight: "10px" }} onClick={this.openCreateModal}>Add Attendee</button>
                        </div>
                    </div>
                    <KfcAttendanceDetailDatatable ref="detailDatatable" id={this.props.id} />
                </Modal.Body>
            </Modal>
        );
    }
});


window.KfcAttendanceListModal = KfcAttendanceListModal;