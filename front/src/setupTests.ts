import { TextDecoder, TextEncoder } from 'util'
import '@testing-library/jest-dom'

// jsdom does not implement TextEncoder/TextDecoder; react-router-dom v7 needs them.
Object.assign(globalThis, { TextEncoder, TextDecoder })
