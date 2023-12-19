import {ViteCommonJSConfig} from '@prosopo/config'
import path from 'path'

export default function () {
    //TODO this will not work out of the box. Please get the ViteCommonJSConfig from @prosopo/config and modify to suit your needs
    return ViteCommonJSConfig('procaptcha-wordpress', path.resolve('./tsconfig.cjs.json'))
}
