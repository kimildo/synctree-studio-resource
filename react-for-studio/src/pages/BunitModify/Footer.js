import React, { Component } from 'react';
import { withRouter, Link } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
/* global global_data */
@withRouter
@inject('bizStore')
@observer
class Footer extends Component {
    render() {
        const { app_id } = this.props.match.params;

        return (
            <>
                <footer className="text-left">
                    {!global_data.partner ? (
                        <Link
                            className="footer-back"
                            to={`/console/apps/bunit/${app_id}`}>
                            Back to Unit List{' '}
                        </Link>
                    ) : (
                        ''
                    )}
                </footer>
            </>
        );
    }
}
export default Footer;
