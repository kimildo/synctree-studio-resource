import axios from 'axios';
import Util from './Util';

const Request = axios.create();
Request.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
};
Request.defaults.timeout = 30000;

Request.interceptors.request.use(
    config => {
        // Util.showSmallBox();
        return config;
    },
    error => {
        console.log('Request error', error);
        return Promise.reject(error);
    }
);

Request.interceptors.response.use(
    response => {
        if (!!response.data.result) {
            if (response.data.result !== 'success') {
                Util.showSmallBox(
                    'error_message',
                    5000,
                    typeof response.data.message === 'undefined'
                        ? response.data.data.message
                        : response.data.message
                );
                if (response.data.result === 'session_expired') {
                    Util.signOut();
                }
                return Promise.reject(response.data);
            }
        }
        // Util.showSmallBox('success_landing');
        return response;
    },
    error => {
        Util.showSmallBox('error');
        console.error('Response error', error);
        return Promise.reject(error);
    }
);

export default Request;
