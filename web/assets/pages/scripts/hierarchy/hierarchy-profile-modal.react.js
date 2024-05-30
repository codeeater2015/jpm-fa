var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HierarchyProfileModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false}  dialogClassName="modal-custom-85"  enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Hierarchy Profile Modal</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <HierarchyProfileDatatable proVoterId={this.props.proVoterId} />
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        console.log("hierarchy profile modal has been loaded");
    },

});


window.HierarchyProfileModal = HierarchyProfileModal;