import Loadable from 'react-loadable';
import Loading from './Loading';

export default function MyLoadable(opts) {
    return Loadable(
        Object.assign(
            {
                loading: Loading,
                delay: 200,
                timeout: 10000,
            },
            opts
        )
    );
}
