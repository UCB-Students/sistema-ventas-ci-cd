import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import axios from "axios";
import Login from "../components/auth/Login.vue";

vi.mock("axios");

describe("Login - inicio de sesión exitoso", () => {
  const localStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
  };

  beforeEach(() => {
    // reset mocks
    vi.clearAllMocks();

    Object.defineProperty(window, "localStorage", {
      value: localStorageMock,
      configurable: true,
    });
  });

  it("realiza login exitoso, guarda token y emite login-success", async () => {
    const fakeUser = { id: 1, name: "Usuario Prueba" };
    const fakeToken = "fake-jwt-token";

    axios.post.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          token: fakeToken,
          user: fakeUser,
        },
      },
    });

    const wrapper = mount(Login);

    // Rellenar formulario
    await wrapper.find('input[type="email"]').setValue("helmerf.mj7@gmail.com");
    await wrapper.find('input[type="password"]').setValue("Fellsing");

    // Enviar formulario
    await wrapper.find("form").trigger("submit.prevent");

    // Esperar a que termine la promesa
    await wrapper.vm.$nextTick();
    await Promise.resolve(); // asegurar flush de promesas

    // Verificar que axios fue llamado con los datos correctos
    expect(axios.post).toHaveBeenCalledWith("/api/v1/auth/login", {
      email: "helmerf.mj7@gmail.com",
      password: "Fellsing",
    });

    // Verificar que se guardó el token y el usuario en localStorage
    expect(localStorageMock.setItem).toHaveBeenCalledWith(
      "auth_token",
      fakeToken
    );
    expect(localStorageMock.setItem).toHaveBeenCalledWith(
      "user_data",
      JSON.stringify(fakeUser)
    );

    // Verificar que se emitió el evento login-success con el usuario
    const emitted = wrapper.emitted("login-success");
    expect(emitted).toBeTruthy();
    expect(emitted[0][0]).toEqual(fakeUser);
  });
});