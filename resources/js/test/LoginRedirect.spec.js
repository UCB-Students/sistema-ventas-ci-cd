import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import App from "../App.vue";

describe("App - redirección auth", () => {
  beforeEach(() => {
    const localStorageMock = {
      getItem: vi.fn(() => null),
      setItem: vi.fn(),
      removeItem: vi.fn(),
      clear: vi.fn(),
    };
    Object.defineProperty(window, "localStorage", {
      value: localStorageMock,
      configurable: true,
    });
  });

  it("muestra el formulario de login si no hay token", () => {
    const wrapper = mount(App);
    expect(wrapper.text()).toContain("Iniciar Sesión");
  });
});